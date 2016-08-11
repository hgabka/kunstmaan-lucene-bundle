<?php

namespace Hgabka\LuceneBundle\EventListener;

use Kunstmaan\NodeSearchBundle\Helper\SearchBoostInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Hgabka\LuceneBundle\Event\IndexDocumentEvent;

class NodeIndexListener
{
    private $boostPageClasses = [];

    private $boostNodeIds = [];

    public function __construct(RegistryInterface $registry)
    {
        $em = $registry->getManager();

        // get boost data once on service init
        $pageClasses = $em->getRepository('KunstmaanNodeBundle:Node')->findAllDistinctPageClasses();
        foreach ($pageClasses as $pageClass) {
            $page = new $pageClass['refEntityName']();

            if ($page instanceof SearchBoostInterface) {
                $this->boostPageClasses[$pageClass['refEntityName']] = $page->getSearchBoost();
            }
        }

        $nodeSearches = $em->getRepository('KunstmaanNodeSearchBundle:NodeSearch')->findAll();
        foreach ($nodeSearches as $nodeSearch) {
            $this->boostNodeIds[$nodeSearch->getNode()->getId()] = $nodeSearch->getBoost();
        }
    }

    public function onIndex(IndexDocumentEvent $event)
    {
        $data = $event->getData();

        if (!$event->hasAllKeys([
            'root_id',
            'node_id',
            'node_translation_id',
            'node_version_id',
            'page_class',
            'type',
            'slug',
        ])) {
            return false;
        }

        $nodeId = $data['node_id'];
        $pageClass = $data['page_class'];

        $boost = null;
        if (isset($this->boostNodeIds[$nodeId])) {
            $boost = $this->boostNodeIds[$nodeId];
        } elseif (isset($this->boostPageClasses[$pageClass])) {
            $boost = $this->boostPageClasses[$pageClass];
        }

        if ($boost !== null) {
            $event->boostMainFields($boost);
        }

        $event->addUnindexedFields($data);
    }
}

<?php

namespace Hgabka\LuceneBundle\EventListener;

use Hgabka\LuceneBundle\Event\IndexDocumentEvent;

class EntityIndexListener
{
    public function onIndex(IndexDocumentEvent $event)
    {
        $data = $event->getData();

        if (!$event->hasAllKeys([
            'entity',
            'route_name',
            'route_parameters',
            '_boost',
        ])) {
            return false;
        }

        $event->boostMainFields($data['_boost']);

        $event->addUnindexedFields($data);
    }
}

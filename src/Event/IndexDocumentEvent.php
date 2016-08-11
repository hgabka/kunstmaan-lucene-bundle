<?php

namespace Hgabka\LuceneBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Hgabka\LuceneBundle\PagerFanta\LuceneAdapter;
use ZendSearch\Lucene\Document;

class IndexDocumentEvent extends Event
{
    /**
     * @var Document
     */
    private $document;

    /**
     * @var mixed
     */
    private $data;

    public function __construct(Document $document, $data)
    {
        $this->document = $document;
        $this->data = $data;
    }

    /**
     * @param Document $document
     *
     * @return $this
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Boost the main indexed fields
     *
     * @param float $boost
     */
    public function boostMainFields($boost)
    {
        $doc = $this->getDocument();

        $doc->getField('title')->boost = $boost;
        $doc->getField('content')->boost = $boost;
    }

    /**
     * Every required key present?
     *
     * @param array $requiredKeys
     *
     * @return bool
     */
    public function hasAllKeys(array $requiredKeys)
    {
        $data = $this->getData();
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add the fields as unindexed
     *
     * @param array $data
     */
    public function addUnindexedFields(array $data)
    {
        $document = $this->getDocument();
        foreach ($data as $key => $value) {
            $document->addField(Document\Field::unIndexed(
                $key,
                !is_string($value) ?  LuceneAdapter::MARKER_SERIALIZED . serialize($value) : $value
            ));
        }
    }
}

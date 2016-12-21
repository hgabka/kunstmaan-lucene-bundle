<?php

namespace Hgabka\LuceneBundle\PagerFanta;

use Kunstmaan\NodeSearchBundle\PagerFanta\Adapter\SearcherRequestAdapterInterface;
use Kunstmaan\NodeSearchBundle\Search\SearcherInterface;
use ZendSearch\Lucene\Search\QueryHit;

class LuceneAdapter implements SearcherRequestAdapterInterface
{
    const MARKER_SERIALIZED = '::SERIALIZED::';

    private $searcher;

    private $result;

    public function __construct(SearcherInterface $searcher)
    {
        $this->searcher = $searcher;
    }

    protected function getResult()
    {
        if (is_null($this->result)) {
            $this->result = [];

            $serializedMarker = static::MARKER_SERIALIZED;
            foreach ($this->searcher->search() as $queryHit) {
                /** @var QueryHit $queryHit */
                $doc = $queryHit->getDocument();
                $arrayData = [];
                foreach ($doc->getFieldNames() as $fieldName) {
                    $val = $doc->getFieldValue($fieldName);
                    if (strpos($val, $serializedMarker) === 0) {
                        $val = unserialize(substr($val, strlen($serializedMarker)));
                    }

                    $arrayData[$fieldName] = $val;
                }

                // create elasticsearch compatible results
                $this->result[] = [
                    '_source' => $arrayData,
                ];
            }
        }

        return $this->result;
    }

    public function getNbResults()
    {
        return count($this->getResult());
    }

    public function getSlice($offset, $length)
    {
        $result = $this->getResult();

        return array_slice(empty($result) ? [] : $result, $offset, $length);
    }

    public function getSuggestions()
    {
        return [];
    }

    public function getFacets()
    {
        return [];
    }

    public function getAggregations()
    {
        return [];
    }
}

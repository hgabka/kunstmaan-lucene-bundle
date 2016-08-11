<?php

namespace Hgabka\LuceneBundle\Lucene;

use ZendSearch\Lucene\Index as BaseIndex;

/**
 * Elasticsearch compatible index for kunstmaan
 */
class Index extends BaseIndex
{
    /**
     * Searchconfigurations call this. Skips some elasticsearch specific calls by returning true.
     *
     * @return bool
     */
    public function exists()
    {
        return true;
    }
}

<?php

namespace Hgabka\LuceneBundle\Lucene;

use ZendSearch\Lucene\Lucene as BaseLucene;

/**
 * Need a custom index class for kunstmaan
 */
class Lucene extends BaseLucene
{
    public static function create($directory)
    {
        return new Index($directory, true);
    }

    public static function open($directory)
    {
        return new Index($directory, false);
    }
}

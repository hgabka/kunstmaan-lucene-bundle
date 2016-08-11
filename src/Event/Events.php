<?php

namespace Hgabka\LuceneBundle\Event;

final class Events
{
    /**
     * @see Hgabka\LuceneBundle\Event\IndexDocumentEvent
     */
    const INDEX_DOCUMENT = 'hgabka_lucene.index_document';

    /**
     * @see Hgabka\LuceneBundle\Event\ConfigureIndexEvent
     */
    const CONFIGURE_INDEX = 'hgabka_lucene.configure_index';
}

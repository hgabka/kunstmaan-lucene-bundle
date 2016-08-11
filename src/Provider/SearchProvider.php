<?php

namespace Hgabka\LuceneBundle\Provider;

use Ivory\LuceneSearchBundle\Model\LuceneManager;
use Kunstmaan\SearchBundle\Provider\SearchProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Hgabka\LuceneBundle\Event as EventsNs;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Exception\InvalidArgumentException as ZendInvalidArgumentException;

class SearchProvider implements SearchProviderInterface
{
    const ID_FIELD_NAME = 'id';

    /**
     * @var LuceneManager
     */
    private $lucene;

    /**
     * @var string
     */
    private $indexDir;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(LuceneManager $lucene, $indexDir, EventDispatcherInterface $eventDispatcher)
    {
        $this->lucene = $lucene;
        $this->indexDir = $indexDir;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getName()
    {
        return 'Lucene';
    }

    /**
     * @return LuceneManager
     */
    public function getClient()
    {
        return $this->lucene;
    }

    public function createIndex($indexName)
    {
        $event = new EventsNs\ConfigureIndexEvent($indexName);
        $event->setPath(rtrim($this->indexDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $indexName);

        $this->eventDispatcher->dispatch(EventsNs\Events::CONFIGURE_INDEX, $event);

        $this->getClient()->setIndex(
            $event->getIndexName(),
            $event->getPath(),
            $event->getAnalyzer(),
            $event->getMaxBufferedDocs(),
            $event->getMaxMergeDocs(),
            $event->getMergeFactor(),
            $event->getPermissions(),
            $event->getAutoOptimized(),
            $event->getQueryParserEncoding()
        );

        return $this->getIndex($indexName);
    }

    public function getIndex($indexName)
    {
        try {
            return $this->getClient()->getIndex($indexName);
        } catch (\InvalidArgumentException $e) {
            return $this->createIndex($indexName);
        }
    }

    public function createDocument($uid, $documentData, $indexName = '', $indexType = '')
    {
        $document = new Document();

        $this->addIdField($document, $uid);
        $document->addField(Document\Field::unStored('indexName', $indexName));

        $requiredFields = [
            'title',
            'content',
        ];
        foreach ($requiredFields as $requiredField) {
            if (!array_key_exists($requiredField, $documentData)) {
                throw new \LogicException('The following fields are always required for indexing: ' . implode(', ', $requiredFields));
            }

            $document->addField(Document\Field::text($requiredField, $documentData[$requiredField]));
            unset($documentData[$requiredField]);
        }

        $viewRolesVal = 'IS_AUTHENTICATED_ANONYMOUSLY';
        if (array_key_exists('view_roles', $documentData)) {
            $this->createArrayStr($documentData['view_roles']);
            unset($documentData['view_roles']);
        }
        $document->addField(Document\Field::unStored('view_roles', $viewRolesVal));

        $event = new EventsNs\IndexDocumentEvent($document, $documentData);
        $this->eventDispatcher->dispatch(EventsNs\Events::INDEX_DOCUMENT, $event);

        return $event->getDocument();
    }

    public function addDocument($indexName, $indexType, $document, $uid)
    {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException(sprintf('%s objects are required for indexing', Document::class));
        }

        if (!$indexName) {
            try {
                $indexName = $document->getFieldValue('indexName');
            } catch (ZendInvalidArgumentException $e) {
            }
        }

        $index = $this->getIndex($indexName);

        try {
            $idVal = $document->getFieldValue(static::ID_FIELD_NAME);

            $this->deleteDocument($indexName, $indexType, $idVal);
        } catch (ZendInvalidArgumentException $e) {
            $this->addIdField($document, $uid);
        }

        $index->addDocument($document);
    }

    public function addDocuments($documents, $indexName = '', $indexType = '')
    {
        foreach ($documents as $document) {
            /** @var $document Document */
            $this->addDocument($indexName, $indexType, $document, null);
        }
    }

    public function deleteDocument($indexName, $indexType, $uid)
    {
        $index = $this->getIndex($indexName);

        if ($hit = $this->findById($indexName, $uid)) {
            $index->delete($hit->document_id);
        }
    }

    public function deleteDocuments($indexName, $indexType, array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteDocument($indexName, $indexType, $id);
        }
    }

    public function deleteIndex($indexName)
    {
		$index = $this->getIndex($indexName);
        $this->getClient()->removeIndex($indexName, true);
    }

    protected function addIdField(Document $document, $id)
    {
        $document->addField(Document\Field::keyword(static::ID_FIELD_NAME, $id));
    }

    /**
     * Create searchable array string for lucene
     *
     * @param array $data
     *
     * @return string
     */
    protected function createArrayStr($data)
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }

        return !empty($data) ? '|' . implode('|', $data) . '|' : '';
    }

    /**
     * Find document by id
     *
     * @param string $indexName
     * @param string $id
     *
     * @return \ZendSearch\Lucene\Search\QueryHit
     */
    protected function findById($indexName, $id)
    {
        $query = sprintf('%s:"%s"', static::ID_FIELD_NAME, $id);
        $result = $this->getIndex($indexName)->find($query);

        return reset($result);
    }
}

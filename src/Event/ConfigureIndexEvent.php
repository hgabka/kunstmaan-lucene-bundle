<?php

namespace Hgabka\LuceneBundle\Event;

use Ivory\LuceneSearchBundle\Model\LuceneManager;
use Symfony\Component\EventDispatcher\Event;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive;

class ConfigureIndexEvent extends Event
{
    /**
     * @var string
     */
    private $indexName;

    private $path;

    private $analyzer = CaseInsensitive::class;

    private $maxBufferedDocs = LuceneManager::DEFAULT_MAX_BUFFERED_DOCS;

    private $maxMergeDocs = LuceneManager::DEFAULT_MAX_MERGE_DOCS;

    private $mergeFactor = LuceneManager::DEFAULT_MERGE_FACTOR;

    private $permissions = LuceneManager::DEFAULT_PERMISSIONS;

    private $autoOptimized = LuceneManager::DEFAULT_AUTO_OPTIMIZED;

    private $queryParserEncoding = 'UTF-8';

    public function __construct($indexName)
    {
        $this->indexName = $indexName;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     *
     * @return ConfigureIndexEvent
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnalyzer()
    {
        return $this->analyzer;
    }

    /**
     * @param mixed $analyzer
     *
     * @return ConfigureIndexEvent
     */
    public function setAnalyzer($analyzer)
    {
        $this->analyzer = $analyzer;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxBufferedDocs()
    {
        return $this->maxBufferedDocs;
    }

    /**
     * @param int $maxBufferedDocs
     *
     * @return ConfigureIndexEvent
     */
    public function setMaxBufferedDocs($maxBufferedDocs)
    {
        $this->maxBufferedDocs = $maxBufferedDocs;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxMergeDocs()
    {
        return $this->maxMergeDocs;
    }

    /**
     * @param int $maxMergeDocs
     *
     * @return ConfigureIndexEvent
     */
    public function setMaxMergeDocs($maxMergeDocs)
    {
        $this->maxMergeDocs = $maxMergeDocs;

        return $this;
    }

    /**
     * @return int
     */
    public function getMergeFactor()
    {
        return $this->mergeFactor;
    }

    /**
     * @param int $mergeFactor
     *
     * @return ConfigureIndexEvent
     */
    public function setMergeFactor($mergeFactor)
    {
        $this->mergeFactor = $mergeFactor;

        return $this;
    }

    /**
     * @return int
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param int $permissions
     *
     * @return ConfigureIndexEvent
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAutoOptimized()
    {
        return $this->autoOptimized;
    }

    /**
     * @param bool $autoOptimized
     *
     * @return ConfigureIndexEvent
     */
    public function setAutoOptimized($autoOptimized)
    {
        $this->autoOptimized = $autoOptimized;

        return $this;
    }

    /**
     * @return string
     */
    public function getQueryParserEncoding()
    {
        return $this->queryParserEncoding;
    }

    /**
     * @param string $queryParserEncoding
     *
     * @return ConfigureIndexEvent
     */
    public function setQueryParserEncoding($queryParserEncoding)
    {
        $this->queryParserEncoding = $queryParserEncoding;

        return $this;
    }
}

<?php

namespace Hgabka\LuceneBundle\Lucene;

use Ivory\LuceneSearchBundle\Model\LuceneManager as BaseLuceneManager;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Storage\Directory\Filesystem as ZfFilesystem;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;

class LuceneManager extends BaseLuceneManager
{

    /** @var array */
    protected $indexes = array();

    /** @var array */
    protected $configs = array();
	
    /**
     * Gets the config for the given lucene identifier.
     *
     * @param string $identifier The lucene identifier.
     *
     * @throws \InvalidArgumentException If the lucene index does not exist.
     *
     * @return array The config.
     */
    protected function getConfig($identifier)
    {
        if (!isset($this->configs[$identifier])) {
            throw new \InvalidArgumentException(sprintf('The lucene index "%s" does not exist.', $identifier));
        }

        return $this->configs[$identifier];
    }
	
    /**
     * Sets a lucene index.
     *
     * @param string  $identifier          The lucene identifier.
     * @param string  $path                The lucene path.
     * @param string  $analyzer            The lucene analyzer class name.
     * @param integer $maxBufferedDocs     The lucene max buffered docs.
     * @param integer $maxMergeDocs        The lucene max merge docs.
     * @param integer $mergeFactor         The lucene merge factor.
     * @param integer $permissions         The lucene permissions.
     * @param boolean $autoOptimized       The lucene auto optimized.
     * @param string  $queryParserEncoding The lucene query parser encoding.
     */
    public function setIndex(
        $identifier,
        $path,
        $analyzer = self::DEFAULT_ANALYZER,
        $maxBufferedDocs = self::DEFAULT_MAX_BUFFERED_DOCS,
        $maxMergeDocs = self::DEFAULT_MAX_MERGE_DOCS,
        $mergeFactor = self::DEFAULT_MERGE_FACTOR,
        $permissions = self::DEFAULT_PERMISSIONS,
        $autoOptimized = self::DEFAULT_AUTO_OPTIMIZED,
        $queryParserEncoding = self::DEFAULT_QUERY_PARSER_ENCODING
    ) {

        $this->configs[$identifier] = array(
            'path'                  => $path,
            'analyzer'              => $analyzer,
            'max_buffered_docs'     => $maxBufferedDocs,
            'max_merge_docs'        => $maxMergeDocs,
            'merge_factor'          => $mergeFactor,
            'permissions'           => $permissions,
            'auto_optimized'        => $autoOptimized,
            'query_parser_encoding' => $queryParserEncoding,
        );
    }
	
    public function getIndex($identifier)
    {
        if (!isset($this->indexes[$identifier])) {
        $config = $this->getConfig($identifier);
        $path = $config['path'];

        if (!$this->checkPath($path)) {
            $this->indexes[$identifier] = Lucene::create($path);
        } else {
            $this->indexes[$identifier] = Lucene::open($path);
        }

        Analyzer::setDefault(new $config['analyzer']());

        $this->indexes[$identifier]->setMaxBufferedDocs($config['max_buffered_docs']);
        $this->indexes[$identifier]->setMaxMergeDocs($config['max_merge_docs']);
        $this->indexes[$identifier]->setMergeFactor($config['merge_factor']);

        ZfFilesystem::setDefaultFilePermissions($config['permissions']);

        if ($config['auto_optimized']) {
            $this->indexes[$identifier]->optimize();
        }

        QueryParser::setDefaultEncoding($config['query_parser_encoding']);
		}
		
        return $this->indexes[$identifier];
    }
	
    /**
     * Removes a lucene index.
     *
     * @param string  $identifier      The lucene identifier.
     * @param boolean $removeDirectory TRUE if the index should be erased else FALSE.
     */
    public function removeIndex($identifier, $removeDirectory = false)
    {
        if ($removeDirectory) {
            $this->eraseIndex($identifier);
        }

        unset($this->configs[$identifier]);

        if (isset($this->indexes[$identifier])) {
            unset($this->indexes[$identifier]);
        }
    }

    /**
     * Erases a lucene index.
     *
     * @param string $identifier The lucene identifier.
     */
    public function eraseIndex($identifier)
    {
        $config = $this->getConfig($identifier);

        $filesystem = new SfFilesystem();
        $filesystem->remove($config['path']);
    }
	
    /**
     * Checks if a lucene index path exists.
     *
     * @param string $path The lucene index path.
     *
     * @return boolean TRUE if the lucene index path exists else FALSE.
     */
    private function checkPath($path)
    {
        return file_exists($path) && is_readable($path) && ($resources = scandir($path)) && (count($resources) > 2);
    }
}

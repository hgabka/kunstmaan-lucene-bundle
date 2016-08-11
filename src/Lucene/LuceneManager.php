<?php

namespace Hgabka\LuceneBundle\Lucene;

use Ivory\LuceneSearchBundle\Model\LuceneManager as BaseLuceneManager;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Storage\Directory\Filesystem as ZfFilesystem;

class LuceneManager extends BaseLuceneManager
{
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
}

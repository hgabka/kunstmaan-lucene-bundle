<?php

namespace Hgabka\LuceneBundle\Configuration;
use Kunstmaan\NodeSearchBundle\Configuration\NodePagesConfiguration as BaseConfiguration;

class NodePagesConfiguration extends BaseConfiguration
{
    /**
     * Create node index
     */
    public function createIndex()
    {
        //create analysis
        $analysis = $this->container->get(
            'kunstmaan_search.search.factory.analysis'
        );

        foreach ($this->locales as $locale) {
            $localeAnalysis = clone($analysis);
            $language = $this->analyzerLanguages[$locale]['analyzer'];

            //build new index
            $index = $this->searchProvider->createIndex($this->indexName . '_' . $locale);

            //create index with analysis
//            $this->setAnalysis($index, $localeAnalysis->setupLanguage($language));

//            $this->setMapping($index, $locale);
        }
    }

}

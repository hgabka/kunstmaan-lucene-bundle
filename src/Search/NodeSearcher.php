<?php

namespace Hgabka\LuceneBundle\Search;

use Kunstmaan\AdminBundle\Entity\BaseUser;
use Kunstmaan\AdminBundle\Helper\DomainConfigurationInterface;
use Kunstmaan\NodeSearchBundle\Search\SearcherInterface;
use Kunstmaan\SearchBundle\Provider\SearchProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NodeSearcher implements SearcherInterface
{
    /**
     * @var SearchProviderInterface
     */
    private $searchProvider;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var DomainConfigurationInterface
     */
    private $domainConfiguration;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var string
     */
    private $indexType;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $query;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $contentType;

    public function __construct(
        SearchProviderInterface $searchProvider,
        TokenStorageInterface $tokenStorage,
        DomainConfigurationInterface $domainConfiguration
    ) {
        $this->searchProvider = $searchProvider;
        $this->tokenStorage = $tokenStorage;
        $this->domainConfiguration = $domainConfiguration;
    }

    /**
     * @return SearchProviderInterface
     */
    public function getSearchProvider()
    {
        return $this->searchProvider;
    }

    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    public function defineSearch($query, $type)
    {
        $query = $this->escapeLucene($query);

        $q = '(';
        foreach (explode(' ', $query) as $term) {
            if (strlen($term) >= 3 && substr($term, 0, 1) !== '"' && substr($term, -1) !== '"') {
                $term .= '*';
            }
            $q .= sprintf('title:%s^2 content:%s ', $term, $term);
        }
        $q .= ')';

        if (!is_null($type)) {
            $q .= sprintf(' AND type:"%s"', $type);
        }

        $rootNode = $this->domainConfiguration->getRootNode();
        if (!is_null($rootNode)) {
            $q .= sprintf('AND root_id:%d', $rootNode->getId());
        }

        $this->applySecurityFilter($q);

        $this->query = $q;

        return $q;
    }

    public function search($offset = null, $size = null)
    {
        $index = $this->getSearchProvider()->getIndex($this->getIndexName() . '_' . $this->getLanguage());

        return $index->find($this->getQuery());
    }

    public function getSuggestions()
    {
        return [];
    }

    public function setPagination($offset, $size)
    {
        $this->offset = $offset;
        $this->size = $size;

        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setLanguage($language)
    {
        $this->language = $language;

        return $this->language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setIndexName($name)
    {
        $this->indexName = $name;

        return $this;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }

    public function setIndexType($indexType)
    {
        $this->indexType = $indexType;

        return $this;
    }

    public function getIndexType()
    {
        return $this->indexType;
    }

    public function setSearch($search)
    {
        $this->searchProvider = $search;

        return $this;
    }

    public function getSearch()
    {
        return $this->searchProvider;
    }

    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    protected function applySecurityFilter(&$q)
    {
        $roles = $this->getCurrentUserRoles();

        $roles = array_map(function ($val) {
            return '"' . $val . '"';
        }, $roles);

        if (trim($q) !== '') {
            $q .= ' AND ';
        }

        $q .= 'view_roles:(';
        $q .= implode(' ', $roles);
        $q .= ')';
    }

    protected function getCurrentUserRoles()
    {
        $roles = [];
        if (!is_null($this->getTokenStorage())) {
            $user = $this->getTokenStorage()->getToken()->getUser();
            if ($user instanceof BaseUser) {
                $roles = $user->getRoles();
            }
        }

        // Anonymous access should always be available for both anonymous & logged in users
        if (!in_array('IS_AUTHENTICATED_ANONYMOUSLY', $roles)) {
            $roles[] = 'IS_AUTHENTICATED_ANONYMOUSLY';
        }

        return $roles;
    }

    /**
     * Escape lucene special characters
     *
     * @link https://lucene.apache.org/core/2_9_4/queryparsersyntax.html#Escaping Special Characters
     * @param string $str
     *
     * @return string
     */
    protected function escapeLucene($str)
    {
        $chars = ['+', '-', '&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':', '\\'];
        // '"' is allowed

        $newStr = '';
        for ($i = 0, $len = strlen($str); $i < $len; $i++) {
            if (in_array($str[$i], $chars, true)) {
                $newStr .= '\\';
            }

            $newStr .= $str[$i];
        }

        return $newStr;
    }
}

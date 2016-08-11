<?php

namespace Hgabka\LuceneBundle\Services;

use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\NodeSearchBundle\Search\SearcherInterface;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Hgabka\LuceneBundle\PagerFanta\LuceneAdapter;

/**
 * Class SearchService
 */
class SearchService
{
    /**
     * @var RenderContext
     *
     */
    protected $renderContext;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var RequestStack
     *
     */
    protected $requestStack;

    /**
     * @var int
     */
    protected $defaultPerPage;

    /**
     * @param ContainerInterface $container
     * @param RequestStack       $requestStack
     * @param int                $defaultPerPage
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack, $defaultPerPage = 10)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->defaultPerPage = $defaultPerPage;
        $this->renderContext = new RenderContext();
    }

    /**
     * @param int $defaultPerPage
     *
     */
    public function setDefaultPerPage($defaultPerPage)
    {
        $this->defaultPerPage = $defaultPerPage;
    }

    /**
     * @return RenderContext
     */
    public function getRenderContext()
    {
        return $this->renderContext;
    }

    /**
     * @param RenderContext $renderContext
     */
    public function setRenderContext($renderContext)
    {
        $this->renderContext = $renderContext;
    }

    /**
     * @return RenderContext
     *
     * @deprecated Use getRenderContext
     */
    public function getRenderContect()
    {
        return $this->renderContext;
    }

    /**
     * @param RenderContext $renderContext
     *
     * @deprecated Use setRenderContext
     */
    public function setRenderContect($renderContext)
    {
        $this->renderContext = $renderContext;
    }

    /**
     * @return int
     */
    public function getDefaultPerPage()
    {
        return $this->defaultPerPage;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return Pagerfanta
     */
    public function search()
    {
        $request = $this->requestStack->getCurrentRequest();

        // Retrieve the current page number from the URL, if not present of lower than 1, set it to 1
        $entity = $request->attributes->get('_entity');

        $pageNumber = $this->getRequestedPage($request);
        /** @var SearcherInterface $searcher */
        $searcher   = $this->container->get($entity->getSearcher());

        $this->applySearchParams($searcher, $request, $this->renderContext);

        // echo this for the lucene search query
        $searcher->defineSearch($searcher->getData(), $searcher->getContentType());

        $adapter    = new LuceneAdapter($searcher);
        $pagerfanta = new Pagerfanta($adapter);
        try {
            $pagerfanta
                ->setMaxPerPage($this->getDefaultPerPage())
                ->setCurrentPage($pageNumber);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $pagerfanta;
    }

    /**
     * @param SearcherInterface $searcher
     * @param Request           $request
     * @param RenderContext     $context
     */
    protected function applySearchParams(SearcherInterface $searcher, Request $request, RenderContext &$context)
    {
        // Retrieve the search parameters
        $queryString = trim($request->query->get('query'));
        $queryType   = $request->query->get('type');
        $lang        = $request->getLocale();

        $context['q_query'] = $queryString;
        $context['q_type']  = $queryType;

        $searcher
            ->setData($queryString)
            ->setContentType($queryType)
            ->setLanguage($lang)
        ;

        return;
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    private function getRequestedPage(Request $request)
    {
        $pageNumber = $request->query->getInt('page', 1);
        if (!$pageNumber || $pageNumber < 1) {
            $pageNumber = 1;
        }

        return $pageNumber;
    }
}

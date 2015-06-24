<?php

namespace xrow\bootstrapBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use xrow\bootstrapBundle\Helper\Content as ContentHelper;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    protected $container;

    /**
     * Contructor of the planet twig extension
     *
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('xrow_content_list', array($this, 'contentList')),
            new \Twig_SimpleFunction('xrow_content_tree', array($this, 'contentTree')),
        );
    }

    /**
     * Return a list of childobjects of a location
     * Example how to use: {{ xrow_content_list(2, array('article', 'infobox', 'folder'), 'priority', 'asc', 10) }}
     *
     * @param int $rootLocationId
     * @param string $classIdentifier
     * @param string $sortField
     * @param string $sortType
     * @param int $limit
     * @param int $offset
     * @param boolean $showHidden
     * @return array
     */
    public function contentList($rootLocationId = 2, $classIdentifier = array('article'), $sortField = 'published', $sortType = Query::SORT_DESC, $limit = null, $offset = 0, $showHidden = false)
    {
        $contentHelper = $this->container->get('xrow.helper.content');
        $children = $contentHelper->contentTree($rootLocationId, $classIdentifier, $sortField, $sortType,
                                                $limit, $offset, $showHidden, 0);
        return $children;
    }

    /**
     * Return a list of childobjects at any level of a location
     * Example how to use for all children: {{ xrow_content_list(2, array('article'), 'name', 'asc', 10) }}
     * Example how to use for children depth 3: {{ xrow_content_list(2, array('article', 'infobox', 'folder), 'name', 'asc', 10, 0, false, 3) }}
     *
     * @param int $rootLocationId
     * @param string $classIdentifier
     * @param string $sortField
     * @param string $sortType
     * @param int $limit
     * @param int $offset
     * @param boolean $showHidden
     * @param boolean|int $depth
     * @return array
     */
    public function contentTree($rootLocationId = 2, $classIdentifier = array('article'), $sortField = 'published', $sortType = Query::SORT_DESC, $limit = null, $offset = 0, $showHidden = false, $depth = false)
    {
        $contentHelper = $this->container->get('xrow.helper.content');
        $children = $contentHelper->contentTree($rootLocationId, $classIdentifier, $sortField, $sortType,
                                                $limit, $offset, $showHidden, $depth);
        return $children;
    }

    public function getName()
    {
        return 'xrowbs_extension';
    }
}
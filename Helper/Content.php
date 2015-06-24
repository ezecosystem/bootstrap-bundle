<?php

namespace xrow\bootstrapBundle\Helper;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class Content
{
    /**
     * @var eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var eZ\Publish\Core\MVC\ConfigResolverInterface;
     */
    protected $configResolver;

    public function __construct(RepositoryInterface $repository, ConfigResolverInterface $configResolver)
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
    }

    /**
     * Searches for content under $parentLocationId being of the specified
     * types sorted with $sortClauses
     *
     * @param int $parentLocationId
     * @param array $typeIdentifiers
     * @param string $sortField
     * @param string $sortType
     * @param int|null $limit
     * @param int $offset
     * @param boolean $showHidden
     * @param boolean|int $depth
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function contentTree($parentLocationId, array $typeIdentifiers, $sortField, $sortType, $limit = null, $offset = 0, $showHidden = false, $depth = false)
    {
        $searchService = $this->repository->getSearchService();
        $query = new Query();
        $criterion = array(
                        new Criterion\ContentTypeIdentifier($typeIdentifiers),
                        new Criterion\LanguageCode($this->configResolver->getParameter('languages'))
                     );
        switch ($depth){
            // get direct children of a loaction
            case 0:
                $criterion[] = new Criterion\ParentLocationId($parentLocationId);
                break;
            // get all children of a location
            case false:
                $criterion[] = new Criterion\Subtree($parentLocation->pathString);
                break;
                // get children of a location with depth
            default:
                $criterion[] = new Criterion\Location\Depth(Criterion\Operator::LTE, $depth);
                $criterion[] = new Criterion\Subtree($parentLocation->pathString);
                break;
        }
        $query->criterion = new Criterion\LogicalAnd($criterion);
        if (!empty($sortField)) {
            switch ($sortField){
                case 'published':
                    $sort = new SortClause\DatePublished($sortType);
                    break;
                case 'priority':
                    $sort = new SortClause\LocationPriority($sortType);
                    break;
                case 'name':
                    $sort = new SortClause\ContentName($sortType);
                    break;
                default:
                    if (count($typeIdentifiers) == 1)
                        $sort = new SortClause\Field($typeIdentifiers[0], $sortField, $sortType);
                    break;
            }
            if (isset($sort))
                $query->sortClauses = $sort;
        }
        $query->limit = $limit;
        $query->offset = $offset;
        if ($showHidden !== false) {
            $query->filter = new Criterion\Visibility(Criterion\Visibility::HIDDEN);
        }
        $results = $searchService->findContent($query);
        $children = array();
        foreach ($results->searchHits as $hit) {
            $children[] = $hit->valueObject;
        }
        return $children;
    }
}
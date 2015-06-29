<?php

namespace xrow\bootstrapBundle\Helper;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
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
        try {
            $location = $this->repository->getLocationService()->loadLocation($parentLocationId);
            if ($location->invisible && $showHidden === false) {
                throw new NotFoundHttpException("Location #$parentLocationId cannot be displayed as it is flagged as invisible.");
            }
            $languages = $this->configResolver->getParameter('languages');
            $searchService = $this->repository->getSearchService();
            $query = new LocationQuery();
            $criterion = array(
                            new Criterion\ContentTypeIdentifier($typeIdentifiers),
                            new Criterion\LanguageCode($languages)
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
                $sortOrder = Query::SORT_ASC;
                if (!empty($sortType)) {
                    if (strtoupper($sortType) == 'DESC' || strtoupper($sortType) == 'DESCENDING' || strtoupper($sortType) == 0)
                        $sortOrder = Query::SORT_DESC;
                }
                switch ($sortField){
                    case 'published':
                        $sort = new Query\SortClause\DatePublished($sortOrder);
                        break;
                    case 'modified':
                        $sort = new Query\SortClause\DateModified($sortOrder);
                    case 'priority':
                        $sort = new Query\SortClause\Location\Priority($sortOrder);
                        break;
                    case 'name':
                        $sort = new Query\SortClause\ContentName($sortOrder);
                        break;
                    default:
                        if (count($typeIdentifiers) == 1)
                            $sort = new Query\SortClause\Field($typeIdentifiers[0], $sortField, $sortOrder);
                        break;
                }
                if (isset($sort))
                    $query->sortClauses = array($sort);
            }
            $query->limit = $limit;
            $query->offset = $offset;
            if ($showHidden !== false) {
                $query->filter = new Criterion\Visibility(Criterion\Visibility::HIDDEN);
            }
            $results = $searchService->findLocations($query);
            $children = array();
            foreach ($results->searchHits as $hit) {
                $children[] = $this->repository->getContentService()->loadContentByContentInfo(
                                                    $hit->valueObject->getContentInfo(),
                                                    $languages);
            }
            return $children;
        }
        catch (Exception $e) {
            die(var_dump($e->getMessage()));
        }
    }
}
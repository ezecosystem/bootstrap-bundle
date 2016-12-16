<?php
 
namespace xrow\bootstrapBundle\ValueObjectVisitor;
 
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Pagerfanta\Pagerfanta;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use xrow\bootstrapBundle\Helper\LocationUtilities;

class RestMenu extends ValueObjectVisitor
{

    protected $urlAliasService;

    protected $locationService;
    
    protected $searchService;
    
    protected $locationUtilities;
    
    protected $nodeCssClassStrings;

    public function __construct( URLAliasService $urlAliasService, LocationService $locationService, LocationUtilities $locationUtilities, SearchService $searchService)
    {
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
        $this->locationUtilities = $locationUtilities;
        $this->searchService = $searchService;
    }

    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $locationId = $data->locationID;
        $rootLocationID = $data->rootLocationID;
        $languages = $data->languages;
        $defaultTTL = $data->defaultTTL;
        $pathPrefixes = $data->pathPrefixes;
        $includeContentTypes = $data->includeContentTypes;
        $this->nodeCssClassStrings = $data->nodeCssClassStrings;
        $limit = 50;
        $contentTypeIdentifier = array('frontpage');

        if ( $locationId !== null )
        {
            $response = new Response();
            $response->setSharedMaxAge( $defaultTTL );
            $response->headers->set( 'X-Location-Id', $locationId );
            $response->setVary( 'X-User-Hash' );
            $location = $this->locationService->loadLocation( $locationId );
            $contentInfo = $location->contentInfo;
            $parentLocations = array();

            if ( $location->invisible )
            {
                throw new NotFoundHttpException( "Location #$locationId cannot be displayed as it is flagged as invisible." );
            }

            $generator->startObjectElement( 'menu' );
                //Current locaion and siblings start
                    $generator->startList( 'siblings' );
                        $siblings = $this::getSiblings($locationId, $limit, $contentTypeIdentifier, $languages, $pathPrefixes, $rootLocationID, $locationId);
                        if ( sizeof($siblings) > 0 ) {
                            foreach ( $siblings as $sibling ) {
                                $generator->startObjectElement( 'sibling' );
                                    $generator->startValueElement( 'name', $sibling["name"] );
                                    $generator->endValueElement( 'name' );
                                    $generator->startValueElement( 'url', $sibling["url"] );
                                    $generator->endValueElement( 'url' );
                                    $generator->startValueElement( 'locationId', $sibling["locationId"] );
                                    $generator->endValueElement( 'locationId' );
                                    $generator->startValueElement( 'priority', $sibling["priority"] );
                                    $generator->endValueElement( 'priority' );
                                    $generator->startValueElement( 'class', $this::getCssClass($sibling["locationId"]) );
                                    $generator->endValueElement( 'class' );
                                $generator->endObjectElement( 'sibling' );
                            }
                        } else {
                            $urlAlias = $this->urlAliasService->reverseLookup( $location );
                            $path = $this->locationUtilities->removePathPrefix( $urlAlias->path, $pathPrefixes);

                            $generator->startObjectElement( "sibling" );
                                $generator->startValueElement( 'name', $contentInfo->name );
                                $generator->endValueElement( 'name' );
                                $generator->startValueElement( 'url', $path );
                                $generator->endValueElement( 'url' );
                                $generator->startValueElement( 'locationId', $locationId );
                                $generator->endValueElement( 'locationId' );
                                $generator->startValueElement( 'priority', $location->priority );
                                $generator->endValueElement( 'priority' );
                                $generator->startValueElement( 'class', $this::getCssClass($locationId) );
                                $generator->endValueElement( 'class' );
                            $generator->endObjectElement( "sibling" );
                        }
                    $generator->endList( 'siblings' );
                //Current locaion and siblings start

                //Children start
                    $children = $this::getChildren($locationId, $limit, $contentTypeIdentifier, $languages, $pathPrefixes );
                    if ( sizeof($children) > 0 ) {
                        $generator->startList( 'children' );
                            foreach ( $children as $child ) {
                                $generator->startObjectElement( 'child' );
                                    $generator->startValueElement( 'name', $child["name"] );
                                    $generator->endValueElement( 'name' );
                                    $generator->startValueElement( 'url', $child["url"] );
                                    $generator->endValueElement( 'url' );
                                    $generator->startValueElement( 'locationId', $child["locationId"] );
                                    $generator->endValueElement( 'locationId' );
                                    $generator->startValueElement( 'priority', $child["priority"] );
                                    $generator->endValueElement( 'priority' );
                                    $generator->startValueElement( 'class', $this::getCssClass($child["locationId"]) );
                                    $generator->endValueElement( 'class' );
                                $generator->endObjectElement( 'child' );
                            }
                        $generator->endList( 'children' );
                    }
                //Children end

                //breadcrumb start
                $parentLocationIds = $this::getPathArray($locationId, $rootLocationID);
                if ( isset($parentLocationIds) && sizeof($parentLocationIds) > 0 ) {
                    $generator->startList( 'ancestors' );
                        foreach ( $parentLocationIds as $parentLocationID ) {
                            $parentLocation = $this->locationService->loadLocation( $parentLocationID );
                            $parentUrlAlias = $this->urlAliasService->reverseLookup( $parentLocation );
                            $parentContentInfo = $parentLocation->contentInfo;
                            $parentPath = $this->locationUtilities->removePathPrefix( $parentUrlAlias->path, $pathPrefixes);
                            $parentName = $parentContentInfo->name;

                            $generator->startObjectElement( "ancestor" );
                                $generator->startValueElement( 'name', $parentName );
                                $generator->endValueElement( 'name' );
                                $generator->startValueElement( 'url', $parentPath );
                                $generator->endValueElement( 'url' );
                                $generator->startValueElement( 'locationId', $parentLocationID );
                                $generator->endValueElement( 'locationId' );
                                $generator->startValueElement( 'class', $this::getCssClass($parentLocationID) );
                                $generator->endValueElement( 'class' );
                            $generator->endObjectElement( "ancestor" );
                        }
                    $generator->endList( 'ancestors' );
                }
                //breadcrumb end

            $generator->endObjectElement( 'menu' );
        }
    }

    private function getPathArray($locationId, $rootLocationID) {
        $location = $this->locationService->loadLocation( $locationId );
        $locationPathArray = array_reverse(explode("/", $location->pathString));
        $result = array();
        foreach ( $locationPathArray as $pathLocationID ) {
            if ( in_array($pathLocationID, array('0', '1', '2')) ) {
                break;
            }
            if ( $pathLocationID != "" && $pathLocationID != $locationId) {
                array_push($result, $pathLocationID);
            }
        }
        return $result;
    }

    private function getSiblings( $locationId, $limit, $contentTypeIdentifier, $languages, $pathPrefixes, $rootLocationID, $currentLocationId ) {
        $parentLocationIdArray = $this::getPathArray($locationId, $rootLocationID);
        if ( sizeof($parentLocationIdArray) > 0) {
            return $this::getChildren($parentLocationIdArray[0], $limit, $contentTypeIdentifier, $languages, $pathPrefixes, $currentLocationId);
        }
        return array();
    }

    private function getChildren($locationId, $limit, $contentTypeIdentifier, $languages, $pathPrefixes, $currentLocationId = false) {
        $location = $this->locationService->loadLocation( $locationId );
        $criteria = array(
            new Criterion\Visibility( Criterion\Visibility::VISIBLE ),
            new Criterion\ParentLocationId( $locationId ),
            new Criterion\ContentTypeIdentifier( $contentTypeIdentifier ),
            new Criterion\LanguageCode( $languages ),
        );
        $criteria = new Criterion\LogicalAnd( $criteria );
        $query = new Query();
        $query->query = $criteria;
        $query->sortClauses = array( $this->locationUtilities->getSortClauseBySortField($location->sortField, $location->sortOrder) );
        $pager = new Pagerfanta(
            new ContentSearchAdapter( $query, $this->searchService )
        );

        $pager->setMaxPerPage( $limit );
        $pager->setCurrentPage( 1 );
        $resultArray = array();

        foreach ($pager as $pagerItem) {
            $pagerContentInfo = $pagerItem->contentInfo;
            $pagerLocationId = $pagerContentInfo->mainLocationId;
            $pagerLocation = $this->locationService->loadLocation( $pagerLocationId );
            $pagerUrlAlias = $this->urlAliasService->reverseLookup( $pagerLocation );

            $pagerName = $pagerContentInfo->name;
            $pagerPath = $this->locationUtilities->removePathPrefix( $pagerUrlAlias->path, $pathPrefixes);
            array_push($resultArray, array('name' => $pagerName, 'url' => $pagerPath, 'locationId' => $pagerLocationId, 'priority' => $pagerLocation->priority) );
        }
        return $resultArray;
    }

    private function removePathPrefix($path, Array $pathPrefixes) {
        foreach ( $pathPrefixes as $pathPrefix ) {
            if ( preg_match("/^".preg_quote($pathPrefix, '/')."/", $path) ) {
                $path = preg_replace("/^".preg_quote($pathPrefix, '/')."/", "", $path);
            }
        }
        if ( $path == "" ) {
            $path = "/";
        }
        return $path;
    }

    private function getCssClass($locationId) {
        $nodeCssClassStrings = $this->nodeCssClassStrings;
        if ( isset($nodeCssClassStrings[$locationId] ) ) {
           return $nodeCssClassStrings[$locationId]['class_string'];
        }
        return "";
    }
}
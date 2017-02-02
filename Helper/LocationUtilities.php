<?php 

namespace xrow\bootstrapBundle\Helper;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query;

class LocationUtilities {
    /**
     * Function getSortClauseBySortField copied from the eZ\Publish\Core\Repository\LocationService class.
     *
     * @copyright Copyright (C) eZ Systems AS. All rights reserved.
     * @license For full copyright and license information view LICENSE file distributed with this source code.
     * @version //autogentag//
     */
    public function getSortClauseBySortField($sortField, $sortOrder = APILocation::SORT_ORDER_ASC)
    {
        $sortOrder = $sortOrder == APILocation::SORT_ORDER_DESC ? Query::SORT_DESC : Query::SORT_ASC;
        switch ($sortField) {
            case APILocation::SORT_FIELD_PATH:
                return new SortClause\Location\Priority($sortOrder);

            case APILocation::SORT_FIELD_PUBLISHED:
                return new SortClause\DatePublished($sortOrder);

            case APILocation::SORT_FIELD_MODIFIED:
                return new SortClause\DateModified($sortOrder);

            case APILocation::SORT_FIELD_SECTION:
                return new SortClause\SectionIdentifier($sortOrder);

            case APILocation::SORT_FIELD_DEPTH:
                return new SortClause\Location\Depth($sortOrder);

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_CLASS_IDENTIFIER:

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_CLASS_NAME:

            case APILocation::SORT_FIELD_PRIORITY:
                return new SortClause\Location\Priority($sortOrder);

            case APILocation::SORT_FIELD_NAME:
                return new SortClause\ContentName($sortOrder);

            //@todo: sort clause not yet implemented
            // case APILocation::SORT_FIELD_MODIFIED_SUBNODE:

            case APILocation::SORT_FIELD_NODE_ID:
                return new SortClause\Location\Id($sortOrder);

            case APILocation::SORT_FIELD_CONTENTOBJECT_ID:
                return new SortClause\ContentId($sortOrder);

            default:
                return new SortClause\Location\Priority($sortOrder);
        }
    }

    public function removePathPrefix($path, Array $pathPrefixes) {
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
}
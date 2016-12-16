<?php
namespace xrow\bootstrapBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use xrow\bootstrapBundle\Values;

class RestMenuController extends Controller
{
    function GetRestMenuAction( $locationID ) {
        $languages = $this->getConfigResolver()->getParameter( 'languages' );
        $defaultTTL = $this->getConfigResolver()->getParameter( 'content.default_ttl' );
        $pathPrefixes = $this->getConfigResolver()->getParameter( 'content.tree_root.excluded_uri_prefixes' );
        $rootLocationID = $this->getConfigResolver()->getParameter( 'content.tree_root.location_id' );
        $includeContentTypes = $this->getConfigResolver()->getParameter( 'include_content_types', 'xrowbootstrap' );
        $nodeCssClassStrings = $this->getConfigResolver()->getParameter( 'css_class_strings', 'xrowbootstrap' );

        return new Values\RestMenu($locationID, $languages, $defaultTTL, $pathPrefixes, $rootLocationID, $includeContentTypes, $nodeCssClassStrings);
    }
}
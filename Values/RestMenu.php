<?php 

namespace xrow\bootstrapBundle\Values;

class RestMenu
{
    public $locationID;

    public function __construct( $locationID, $languages, $defaultTTL, $pathPrefixes, $rootLocationID, $includeContentTypes, $nodeCssClassStrings )
    {
        $this->locationID = $locationID;
        $this->languages = $languages;
        $this->defaultTTL = $defaultTTL;
        $this->pathPrefixes = $pathPrefixes;
        $this->rootLocationID = $rootLocationID;
        $this->includeContentTypes = $includeContentTypes;
        $this->nodeCssClassStrings = $nodeCssClassStrings;
    }
}
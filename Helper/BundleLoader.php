<?php
namespace xrow\bootstrapBundle\Helper;

class BundleLoader
{
    function append(Array &$bundles)
    {
        $list = array(
            new Xrow\BugReportingBundle\XrowBugReportingBundle(),
            new Xrow\KubernetesDiscoveryBundle\xrowKubernetesDiscoveryBundle(),
            new Xrow\SitemapBundle\xrowSitemapBundle(),
            new Xrow\Jsonld\JsonldBundle(),
            new Xrow\Tracking\TrackingBundle(),
            new xrow\bootstrapBundle\xrowbootstrapBundle()
        );
        $bundles = array_merge($bundles, $list);
    }
}
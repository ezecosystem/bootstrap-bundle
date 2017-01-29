<?php

namespace xrow\bootstrapBundle\LegacyMapper;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Maps configuration parameters to the legacy parameters
 * Similar to https://github.com/ezsystems/LegacyBridge/blob/master/bundle/LegacyMapper/Configuration.php
 */
class AdditionalConfig implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        if ( class_exists("\eZ\Publish\Core\MVC\Legacy\LegacyEvents") ){
            return array(
                \eZ\Publish\Core\MVC\Legacy\LegacyEvents::PRE_BUILD_LEGACY_KERNEL => array( "onBuildKernel", 129 )
            );
        }
        return array();
    }

    /**
     * Adds settings to the parameters that will be injected into the legacy kernel
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent $event
     */
    public function onBuildKernel( PreBuildKernelEvent $event )
    {
        $settings['site.ini/MailSettings/TransportServer'] = $this->container->getParameter( 'xrowbootstrap.mailsettings.TransportServer' );
        $settings['solr.ini/SolrBase/SearchServerURI'] = $this->container->getParameter( 'xrowbootstrap.solr.BaseSearchServerURI' );
        $settings['solr.ini/SolrBaseEvents/SearchServerURI'] = $this->container->getParameter( 'xrowbootstrap.solr.EventSearchServerURI' );
        $settings['file.ini/eZDFSClusteringSettings/DBHost'] = $this->container->getParameter( 'xrowbootstrap.cluster.DBHost' );

        $event->getParameters()->set(
            "injected-settings",
            $settings + (array)$event->getParameters()->get( "injected-settings" )
            );
    }
}
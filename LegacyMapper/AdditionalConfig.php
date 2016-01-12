<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace xrow\bootstrapBundle\LegacyMapper;

use eZ\Publish\Core\FieldType\Image\AliasCleanerInterface;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use ezpEvent;
use ezxFormToken;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RuntimeException;

/**
 * Maps configuration parameters to the legacy parameters
 */
class AdditionalConfig extends ContainerAware implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger
     */
    private $gatewayCachePurger;

    /**
     * @var \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger
     */
    private $persistenceCachePurger;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator
     */
    private $urlAliasGenerator;

    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $legacyDbHandler;

    /**
     * @var array
     */
    private $options;

    /**
     * Disables the feature when set using setEnabled()
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @var AliasCleanerInterface
     */
    private $aliasCleaner;

    public function __construct(
        ConfigResolverInterface $configResolver,
        GatewayCachePurger $gatewayCachePurger,
        PersistenceCachePurger $persistenceCachePurger,
        UrlAliasGenerator $urlAliasGenerator,
        DatabaseHandler $legacyDbHandler,
        AliasCleanerInterface $aliasCleaner,
        array $options = array()
    )
    {
        $this->configResolver = $configResolver;
        $this->gatewayCachePurger = $gatewayCachePurger;
        $this->persistenceCachePurger = $persistenceCachePurger;
        $this->urlAliasGenerator = $urlAliasGenerator;
        $this->legacyDbHandler = $legacyDbHandler;
        $this->aliasCleaner = $aliasCleaner;
        $this->options = $options;
    }

    /**
     * Toggles the feature
     *
     * @param bool $isEnabled
     */
    public function setEnabled( $isEnabled )
    {
        $this->enabled = (bool)$isEnabled;
    }

    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL => array( "onBuildKernel", 128 )
        );
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

        $event->getParameters()->set(
            "injected-settings",
            $settings + (array)$event->getParameters()->get( "injected-settings" )
        );

        if ( class_exists( 'ezxFormToken' ) )
        {
            // Inject csrf protection settings to make sure legacy & symfony stack work together
            if (
                $this->container->hasParameter( 'form.type_extension.csrf.enabled' ) &&
                $this->container->getParameter( 'form.type_extension.csrf.enabled' )
            )
            {
                ezxFormToken::setSecret( $this->container->getParameter( 'kernel.secret' ) );
                ezxFormToken::setFormField( $this->container->getParameter( 'form.type_extension.csrf.field_name' ) );
            }
            // csrf protection is disabled, disable it in legacy extension as well.
            else
            {
                ezxFormToken::setIsEnabled( false );
            }
        }

        // Register http cache content/cache event listener
        $ezpEvent = ezpEvent::getInstance();
        $ezpEvent->attach( 'content/cache', array( $this->gatewayCachePurger, 'purge' ) );
        $ezpEvent->attach( 'content/cache/all', array( $this->gatewayCachePurger, 'purgeAll' ) );

        // Register persistence cache event listeners
        $ezpEvent->attach( 'content/cache', array( $this->persistenceCachePurger, 'content' ) );
        $ezpEvent->attach( 'content/cache/all', array( $this->persistenceCachePurger, 'all' ) );
        $ezpEvent->attach( 'content/class/cache/all', array( $this->persistenceCachePurger, 'contentType' ) );
        $ezpEvent->attach( 'content/class/cache', array( $this->persistenceCachePurger, 'contentType' ) );
        $ezpEvent->attach( 'content/class/group/cache', array( $this->persistenceCachePurger, 'contentTypeGroup' ) );
        $ezpEvent->attach( 'content/section/cache', array( $this->persistenceCachePurger, 'section' ) );
        $ezpEvent->attach( 'user/cache/all', array( $this->persistenceCachePurger, 'user' ) );
        $ezpEvent->attach( 'content/translations/cache', array( $this->persistenceCachePurger, 'languages' ) );

        // Register image alias removal listeners
        $ezpEvent->attach( 'image/removeAliases', array( $this->aliasCleaner, 'removeAliases' ) );
        $ezpEvent->attach( 'image/trashAliases', array( $this->aliasCleaner, 'removeAliases' ) );
        $ezpEvent->attach( 'image/purgeAliases', array( $this->aliasCleaner, 'removeAliases' ) );
    }
}
<?php

namespace xrow\bootstrapBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class xrowbootstrapExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
        public function load( array $configs, ContainerBuilder $container )
        {
            $configuration = $this->getConfiguration( $configs, $container );
            $config = $this->processConfiguration( $configuration, $configs );
    
            $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__.'/../Resources/config' ) );
            $loader->load( 'default_settings.yml' );
    
            // "acme_demo" will be the namespace as used in ConfigResolver format.
            $processor = new ConfigurationProcessor( $container, 'acme_demo' );
            $processor->mapConfig(
                $config,
                // Any kind of callable can be used here.
                // It will be called for each declared scope/SiteAccess.
                function ( $scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
                {
                    // Will map "hello" setting to "acme_demo.<$currentScope>.hello" container parameter
                    // It will then be possible to retrieve this parameter through ConfigResolver in the application code:
                    // $helloSetting = $configResolver->getParameter( 'hello', 'acme_demo' );
                    $contextualizer->setContextualParameter( 'hello', $currentScope, $scopeSettings['hello'] );
                }
            );
    
            // Now map "foo_setting" and ensure keys defined for "my_siteaccess" overrides the one for "my_siteaccess_group"
            // It is done outside the closure as it is needed only once.
            $processor->mapConfigArray( 'foo_setting', $config );
        }

    /*public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }*/
}

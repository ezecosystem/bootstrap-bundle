<?php

namespace xrow\bootstrapBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;

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
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration( $configuration, $configs );

        //siteaccess aware configuration
        $processor = new ConfigurationProcessor( $container, 'xrowbootstrap' );
        $processor->mapSetting( 'include_content_types', $config );
        $processor->mapSetting( 'css_class_strings', $config );

        //@todo find a better way to load non siteaccess aware configuration
        foreach ($config as $key => $value) {
            if ( $key != 'system' ) {
                if ( is_array($value) || is_object($value) ) {
                    foreach ($value as $key2 => $value2) {
                        $container->setParameter($this->getAlias().'.'.$key.'.'.$key2, $value2);
                    }
                }
                else {
                    $container->setParameter($this->getAlias().'.'.$key, $value);
                }
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
<?php

namespace xrow\bootstrapBundle\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class xrowbootstrapExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        // Overwrite data
        if (null !== $config['options']['show_navigation_identifier']) {
            $container->setParameter('xrowbootstrap.show_navigation_identifier', $config['options']['show_navigation_identifier']);
        }
    }

    /**
     * Loads bootstrap configuration
     *
     * @param ContainerBuilder $container
     */
    public function prepend( ContainerBuilder $container )
    {
        $configFile = __DIR__ . '/../Resources/config/xrowbootstrap.yml';
        $config = Yaml::parse( file_get_contents( $configFile ) );

        $container->prependExtensionConfig( 'xrowbootstrap', $config );
        $container->addResource( new FileResource( $configFile ) );
    }
}
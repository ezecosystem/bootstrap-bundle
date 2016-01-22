<?php

namespace xrow\bootstrapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $rootIdentifier;

    /**
     * @param string $rootIdentifier
     */
    public function __construct($rootIdentifier)
    {
        $this->rootIdentifier = $rootIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->rootIdentifier);
        $rootNode
            ->children()
                ->scalarNode('show_navigation_identifier')->defaultNull()->end()
                ->arrayNode( 'mailsettings' )
                    ->children()
                        ->scalarNode( 'TransportServer' )->end()
                    ->end()
                ->end()
                ->arrayNode( 'solr' )
                    ->children()
                        ->scalarNode( 'BaseSearchServerURI' )->end()
                        ->scalarNode( 'EventSearchServerURI' )->end()
                    ->end()
                ->end()
                ->arrayNode( 'cluster' )
                    ->children()
                        ->scalarNode( 'DBHost' )->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
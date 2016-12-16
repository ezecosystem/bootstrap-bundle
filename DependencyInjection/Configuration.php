<?php

namespace xrow\bootstrapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration extends SiteAccessConfiguration implements ConfigurationInterface
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

        $systemNode = $this->generateScopeBaseNode( $rootNode );
        $systemNode
            ->arrayNode( 'include_content_types' )
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode( 'css_class_strings' )
                ->useAttributeAsKey('locationId')
                ->prototype('array')
                    ->children()
                        ->integerNode('locationId')->min(2)->end()
                        ->scalarNode('class_string')->end()
                    ->end()
                ->end()
            ->end();

        $rootNode
            ->children()
                ->scalarNode('show_navigation_identifier')->defaultNull()->end()
                ->arrayNode( 'mailsettings' )
                    ->children()
                        ->scalarNode( 'TransportServer' )->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode( 'solr' )
                    ->children()
                        ->scalarNode( 'BaseSearchServerURI' )->defaultNull()->end()
                        ->scalarNode( 'EventSearchServerURI' )->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode( 'cluster' )
                    ->children()
                        ->scalarNode( 'DBHost' )->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
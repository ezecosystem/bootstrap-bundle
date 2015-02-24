<?php

namespace xrow\bootstrapBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration extends SiteAccessConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'acme_demo' );

        // $systemNode will then be the root of siteaccess aware settings.
        $systemNode = $this->generateScopeBaseNode( $rootNode );
        $systemNode
        ->scalarNode( 'hello' )->isRequired()->end()
        ->arrayNode( 'foo_setting' )
        ->children()
        ->scalarNode( "foo" )->end()
        ->scalarNode( "some" )->end()
        ->integerNode( "an_integer" )->end()
        ->booleanNode( "enabled" )->end()
        ->end()
        ->end();

        return $treeBuilder;
    }

    /*public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('xrowbootstrap');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }*/
}

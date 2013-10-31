<?php

namespace Tritoq\Bundle\ShippingBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tritoq_shipping');

        $rootNode->children()
            ->arrayNode('correios')
            ->children()
            ->scalarNode('company')
            ->end()
            ->scalarNode('services')
            ->end()
            ->scalarNode('password')
            ->end()
            ->arrayNode('parameters')
            ->addDefaultChildrenIfNoneSet()
            ->prototype('scalar')
            ->defaultValue(array())
            ->end();

        $rootNode->children()
            ->arrayNode('jadlog')
            ->children()
            ->scalarNode('user')
            ->end()
            ->scalarNode('services')
            ->end()
            ->scalarNode('password')
            ->end()
            ->arrayNode('parameters')
            ->addDefaultChildrenIfNoneSet()
            ->prototype('scalar')
            ->defaultValue(array())
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}

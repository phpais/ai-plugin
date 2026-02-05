<?php

namespace Phpais\AiPlugin\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ai');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default')->defaultValue('wenxin')->end()
                ->arrayNode('providers')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('api_key')->isRequired()->end()
                            ->scalarNode('model')->isRequired()->end()
                            ->scalarNode('endpoint')->isRequired()->end()
                            ->integerNode('timeout')->defaultValue(30)->end()
                            ->scalarNode('provider')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagProvider;

class FeatureFlagConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('feature_flags');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('provider')
                    ->defaultValue(FeatureFlagProvider::class)
                ->end()
                ->arrayNode('storage')
                        ->addDefaultsIfNotSet()
                        ->children()
                        ->scalarNode('type')->defaultValue('json')->end()
                        ->scalarNode('path')->defaultValue(null)->end()
                    ->end()
                ->end()
                ->booleanNode('cache')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}

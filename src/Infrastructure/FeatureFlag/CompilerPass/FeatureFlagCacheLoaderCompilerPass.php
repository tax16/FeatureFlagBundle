<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\Decorator\FeatureFlagLoaderCacheDecorator;

class FeatureFlagCacheLoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('feature_flag_loader');

        foreach ($taggedServices as $id => $tags) {
            $decoratedServiceId = $id.'.decorator';
            $container->register($decoratedServiceId, FeatureFlagLoaderCacheDecorator::class)
                ->setDecoratedService($id)
                ->setArgument('$loader', new Reference($decoratedServiceId.'.inner'))
                ->setAutowired(true)
                ->setPublic(true);
        }
    }
}

<?php

namespace Tax16\FeatureFlagBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\FeatureFlagCacheLoaderCompilerPass;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\FeatureFlagClassSwitchCompilerPass;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\FeatureFlagMethodSwitchCompilerPass;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\FeatureFlagConfigProviderCompilerPass;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\FeatureFlagStatusCheckCompilerPass;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\DependencyInjection\FeatureFlagExtension;

class FeatureFlagBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->addCompilerPass(new FeatureFlagConfigProviderCompilerPass())
            ->addCompilerPass(new FeatureFlagMethodSwitchCompilerPass())
            ->addCompilerPass(new FeatureFlagCacheLoaderCompilerPass())
            ->addCompilerPass(new FeatureFlagClassSwitchCompilerPass())
            ->addCompilerPass(new FeatureFlagStatusCheckCompilerPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new FeatureFlagExtension();
    }
}

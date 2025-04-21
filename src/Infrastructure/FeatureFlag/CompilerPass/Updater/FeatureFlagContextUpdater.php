<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class FeatureFlagContextUpdater
{
    /**
     * @param array<mixed> $context
     */
    protected function updateContextClassToPublic(array $context, ContainerBuilder $container): void
    {
        foreach ($context as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $definition = $container->getDefinition($serviceId);

                if (!$definition->isPublic()) {
                    $definition->setPublic(true);
                }
            } elseif ($container->hasAlias($serviceId)) {
                $alias = $container->getAlias($serviceId);
                if (!$alias->isPublic()) {
                    $alias->setPublic(true);
                }
            }
        }
    }
}

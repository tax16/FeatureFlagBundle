<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater\FeatureFlagContextUpdater;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\ProxyFactory\FeatureFlagStatusProxyFactory;

class FeatureFlagStatusCheckCompilerPass extends FeatureFlagContextUpdater implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $factoryReference = new Reference(FeatureFlagStatusProxyFactory::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if (!$class || !class_exists($class)) {
                continue;
            }

            if (FeatureFlagAttributeProvider::provideClassStatusAttributeConfig($definition->getClass())) {
                $definition->setFactory([$factoryReference, 'createByClass']);
                $definition->setArguments([
                    new Reference($class)
                ]);

                continue;
            }

            $reflection = new \ReflectionClass($class);
            foreach ($reflection->getMethods() as $method) {
                $config = FeatureFlagAttributeProvider::provideMethodStatusAttributeConfig($method);
                if (!$config) {
                    continue;
                }
                $originalDefinition = clone $definition;
                $originalServiceId = $id.'.original';
                $container->setDefinition($originalServiceId, $originalDefinition);

                $proxyDefinition = new Definition($class);
                $proxyDefinition->setFactory([$factoryReference, 'createByMethod']);
                $proxyDefinition->setArguments([
                    new Reference($originalServiceId),
                ]);

                $container->setDefinition($id, $proxyDefinition);
            }

        }
    }
}
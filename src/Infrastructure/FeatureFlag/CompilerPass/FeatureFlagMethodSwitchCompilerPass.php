<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\ClassFeatureProvider;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater\FeatureFlagContextUpdater;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Proxy\SwitchMethodProxyFactory;

class FeatureFlagMethodSwitchCompilerPass extends FeatureFlagContextUpdater implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(SwitchMethodProxyFactory::class)) {
            return;
        }

        $factoryReference = new Reference(SwitchMethodProxyFactory::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if (!$class || !class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            foreach ($reflection->getMethods() as $method) {
                $config = ClassFeatureProvider::provideMethodAttributeConfig($method);
                if (!$config) {
                    continue;
                }
                $originalDefinition = clone $definition;
                $originalServiceId = $id.'.original';
                $container->setDefinition($originalServiceId, $originalDefinition);

                $proxyDefinition = new Definition($class);
                $proxyDefinition->setFactory([$factoryReference, 'createProxy']);
                $proxyDefinition->setArguments([
                    new Reference($originalServiceId),
                ]);

                $container->setDefinition($id, $proxyDefinition);

                $this->updateContextClassToPublic($config->context, $container);
            }
        }
    }
}

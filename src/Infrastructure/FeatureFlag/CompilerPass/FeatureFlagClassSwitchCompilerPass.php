<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\ClassFeatureProvider;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater\FeatureFlagContextUpdater;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Proxy\SwitchClassProxyFactory;

class FeatureFlagClassSwitchCompilerPass extends FeatureFlagContextUpdater implements CompilerPassInterface
{
    /**
     * @throws \Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $proxyFactoryRef = new Reference(SwitchClassProxyFactory::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            /** @var string|null $class */
            $class = $definition->getClass();

            if (!$class || !class_exists($class)) {
                continue;
            }

            if (!$switchConfig = ClassFeatureProvider::provideClassAttributeConfig($definition->getClass())) {
                continue;
            }

            $switchedClass = $switchConfig->switchedClass;

            if (!class_exists($switchedClass)) {
                throw new \LogicException("The switched class '{$switchedClass}' does not exist.");
            }

            $originalDefinition = clone $definition;
            $originalServiceId = $id.'.original';
            $container->setDefinition($originalServiceId, $originalDefinition);

            $proxyDefinition = new Definition($class);
            $proxyDefinition->setFactory([$proxyFactoryRef, 'createProxy']);
            $proxyDefinition->setArguments([
                new Reference($originalServiceId),
                new Reference($switchedClass),
            ]);

            $container->setDefinition($id, $proxyDefinition);

            $this->updateContextClassToPublic($switchConfig->context, $container);
        }
    }
}

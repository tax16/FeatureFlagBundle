<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory\SwitchClassProxyFactory;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater\FeatureFlagContextUpdater;

class FeatureFlagClassSwitchCompilerPass extends FeatureFlagContextUpdater implements CompilerPassInterface
{
    /**
     * @throws \Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $proxyFactoryRef = new Reference(SwitchClassProxyFactory::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isAbstract()) {
                continue;
            }

            /** @var string|null $class */
            $class = $definition->getClass();

            if (!$class || !class_exists($class)) {
                continue;
            }

            if (!$switchConfig = FeatureFlagAttributeProvider::provideClassAttributeConfig($definition->getClass())) {
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

            $isController = $definition->hasTag('controller.service_arguments');
            if ($isController) {
                $proxyDefinition->setPublic(true);
            }

            $container->setDefinition($id, $proxyDefinition);

            $this->updateContextClassToPublic($switchConfig->context, $container);
        }
    }
}

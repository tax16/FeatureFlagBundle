<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory\SwitchMethodProxyFactory;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater\FeatureFlagContextUpdater;

class FeatureFlagMethodSwitchCompilerPass extends FeatureFlagContextUpdater implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(SwitchMethodProxyFactory::class)) {
            return;
        }

        $factoryReference = new Reference(SwitchMethodProxyFactory::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isAbstract()) {
                continue;
            }

            $class = $definition->getClass();
            if (!$class || !class_exists($class)) {
                continue;
            }

            if (is_subclass_of($class, AbstractController::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);

            foreach ($reflection->getMethods() as $method) {
                $config = FeatureFlagAttributeProvider::provideMethodAttributeConfig($method);
                if (!$config) {
                    continue;
                }

                if ($reflection->isFinal()) {
                    throw new \InvalidArgumentException("Can't create a proxy for a 'final' class: ".$class);
                }

                $originalDefinition = clone $definition;
                $originalServiceId = $id.'.original';
                $container->setDefinition($originalServiceId, $originalDefinition);

                $proxyDefinition = new Definition($class);
                $proxyDefinition->setFactory([$factoryReference, 'createProxy']);
                $proxyDefinition->setArguments([
                    new Reference($originalServiceId),
                ]);

                $isController = $definition->hasTag('controller.service_arguments');
                if ($isController) {
                    $proxyDefinition->setPublic(true);
                }

                $container->setDefinition($id, $proxyDefinition);

                $this->updateContextClassToPublic($config->context, $container);
            }
        }
    }
}

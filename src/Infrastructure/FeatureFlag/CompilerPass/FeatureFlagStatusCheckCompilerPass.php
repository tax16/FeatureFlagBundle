<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory\StatusCheckProxyFactory;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater\FeatureFlagContextUpdater;

class FeatureFlagStatusCheckCompilerPass extends FeatureFlagContextUpdater implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $factoryReference = new Reference(StatusCheckProxyFactory::class);

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isAbstract()) {
                continue;
            }

            $isController = $definition->hasTag('controller.service_arguments');
            if ($isController) {
                continue;
            }

            $class = $definition->getClass();
            if (!$class || !class_exists($class)) {
                continue;
            }

            if (is_subclass_of($class, AbstractController::class)) {
                continue;
            }

            if ($config = FeatureFlagAttributeProvider::provideClassStatusAttributeConfig($definition->getClass())) {
                $this->createProxyDefinition($definition, $id, $container, $class, $factoryReference, 'createByClass', $config->context);
                continue;
            }

            $reflection = new \ReflectionClass($class);
            foreach ($reflection->getMethods() as $method) {
                $config = FeatureFlagAttributeProvider::provideMethodStatusAttributeConfig($method);
                if (!$config) {
                    continue;
                }
                $this->createProxyDefinition($definition, $id, $container, $class, $factoryReference, 'createByMethod', $config->context);
            }

        }
    }

    /**
     * @param array<FeatureFlagContextInterface> $context
     */
    public function createProxyDefinition(
        Definition $definition,
        string $id,
        ContainerBuilder $container,
        string $class,
        Reference $factoryReference,
        string $functionName,
        array $context = [],
    ): void {
        $originalDefinition = clone $definition;
        $originalServiceId = $id.'.original';
        $container->setDefinition($originalServiceId, $originalDefinition);

        $proxyDefinition = new Definition($class);
        $proxyDefinition->setFactory([$factoryReference, $functionName]);
        $proxyDefinition->setArguments([
            new Reference($originalServiceId),
        ]);

        $container->setDefinition($id, $proxyDefinition);

        $this->updateContextClassToPublic($context, $container);
    }
}

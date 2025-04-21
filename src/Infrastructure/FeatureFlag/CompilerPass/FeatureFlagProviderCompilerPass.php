<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\Decorator\FeatureFlagProviderContextDecorator;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;

class FeatureFlagProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('feature_flags.provider')) {
            return;
        }

        /**
         * @var string $providerClass
         */
        $providerClass = $container->getParameter('feature_flags.provider');

        if (!class_exists($providerClass)) {
            throw new \LogicException(sprintf('The specified feature flag provider class "%s" does not exist.', $providerClass));
        }

        if (!$container->hasDefinition($providerClass)) {
            $definition = new Definition($providerClass);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->setPublic(false);
            $container->setDefinition($providerClass, $definition);
        }

        $container->setAlias(FeatureFlagProviderInterface::class, $providerClass)->setPublic(false);

        $this->decorateFeatureFlagProvider($container);
    }

    /**
     * Decorate the FeatureFlagProviderInterface with the FeatureFlagProviderDecorator.
     */
    private function decorateFeatureFlagProvider(ContainerBuilder $container): void
    {
        if (!$container->has(FeatureFlagProviderInterface::class)) {
            return;
        }

        $decoratorDefinition = $container->register(FeatureFlagProviderContextDecorator::class)
            ->setClass(FeatureFlagProviderContextDecorator::class)
            ->setDecoratedService(FeatureFlagProviderInterface::class)
            ->setAutowired(true)
            ->setPublic(true);

        $decoratorDefinition->setArgument(0, new Reference(FeatureFlagProviderContextDecorator::class.'.inner'));
    }
}

<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\Decorator\FeatureFlagProviderContextDecorator;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository\FeatureFlagRepositoryInterface;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\DoctrineFeatureFlagLoader;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Persistence\DoctrineFeatureFlagRepository;

class FeatureFlagConfigProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('feature_flags.provider')
            || !$container->hasParameter('feature_flags.storage.type')
        ) {
            return;
        }

        /**
         * @var string $providerClass
         */
        $providerClass = $container->getParameter('feature_flags.provider');
        $storageType = $container->getParameter('feature_flags.storage.type');

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

        if (FeatureFlagProvider::class === $providerClass) {
            $this->decorateFeatureFlagProvider($container);
        }

        if ($storageType === FeatureFlagStorageType::DOCTRINE->value) {
            if (!interface_exists(\Doctrine\ORM\EntityManagerInterface::class)) {
                throw new \LogicException('Doctrine support requires doctrine/orm package.');
            }

            $this->createDoctrineConfigClass($container);
        }
    }

    private function createDoctrineConfigClass(ContainerBuilder $container): void
    {
        $definition = new Definition(DoctrineFeatureFlagLoader::class);
        $definition->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('feature_flag_loader');
        $container->setDefinition('tax16.feature_flag.doctrine.loader', $definition);

        $definition = new Definition(DoctrineFeatureFlagRepository::class);
        $definition->setAutowired(true)
            ->setAutoconfigured(true)
            // @phpstan-ignore-next-line
            ->addArgument(new Reference(\Doctrine\ORM\EntityManagerInterface::class))
            ->addTag('app.feature_flag_repository');
        $container->setDefinition('tax16.feature_flag.repository.doctrine', $definition);

        $container->setAlias(
            FeatureFlagRepositoryInterface::class,
            'tax16.feature_flag.repository.doctrine'
        )->setPublic(false);
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

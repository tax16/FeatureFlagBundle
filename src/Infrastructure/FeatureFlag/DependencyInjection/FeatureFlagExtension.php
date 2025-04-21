<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\DoctrineFeatureFlagLoader;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Persistence\DoctrineFeatureFlagRepository;

class FeatureFlagExtension extends Extension
{
    public function getAlias(): string
    {
        return 'feature_flags';
    }

    /**
     * @param mixed[] $configs
     *
     * @return void
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new FeatureFlagConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('feature_flags.provider', $config['provider']);
        $container->setParameter('feature_flags.cache', $config['cache']);
        $container->setParameter('feature_flags.storage.path', $config['storage']['path']);
        $container->setParameter('feature_flags.storage.type', $config['storage']['type']);

        if ($config['storage']['type'] === FeatureFlagStorageType::DOCTRINE->value) {
            if (!interface_exists(\Doctrine\ORM\EntityManagerInterface::class)) {
                throw new \LogicException('Doctrine support requires doctrine/orm package.');
            }

            $definition = new Definition(DoctrineFeatureFlagRepository::class);
            $definition->setAutowired(true)
                ->setAutoconfigured(true)
                ->addTag('app.feature_flag_repository');
            $container->setDefinition('tax16.feature_flag.repository.doctrine', $definition);

            $definition = new Definition(DoctrineFeatureFlagLoader::class);
            $definition->setAutowired(true)
                ->setAutoconfigured(true)
                ->addTag('feature_flag_loader');
            $container->setDefinition('tax16.feature_flag.doctrine.loader', $definition);
        }
    }
}

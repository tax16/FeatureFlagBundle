<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
        $container->setParameter('feature_flags.ttl', $config['ttl']);
    }
}

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
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new FeatureFlagConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setFeatureFlagParameters($container, $config);
    }

    /**
     * @param mixed[] $config
     */
    private function setFeatureFlagParameters(ContainerBuilder $container, array $config, string $prefix = 'feature_flags'): void
    {
        foreach ($config as $key => $value) {
            $paramName = "$prefix.$key";

            if (is_array($value)) {
                $this->setFeatureFlagParameters($container, $value, $paramName);
            } else {
                $container->setParameter($paramName, $value);
            }
        }
    }
}

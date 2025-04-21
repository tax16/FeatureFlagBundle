<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Loader\FeatureFlagLoaderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ConfigurationProviderInterface;

class FeatureFlagLoaderFactory
{
    /**
     * @var array<FeatureFlagLoaderFactoryInterface>
     */
    private array $loaders;
    private ConfigurationProviderInterface $parameters;

    /**
     * @param iterable<FeatureFlagLoaderFactoryInterface> $loaders
     */
    public function __construct(ConfigurationProviderInterface $parameters, iterable $loaders)
    {
        $this->parameters = $parameters;
        $this->loaders = iterator_to_array($loaders);
    }

    public function create(): FeatureFlagLoaderInterface
    {
        /**
         * @var string $storageType
         */
        $storageType = $this->parameters->get('feature_flags.storage.type');

        foreach ($this->loaders as $loader) {
            if ($loader->supports($storageType)) {
                return $loader;
            }
        }

        throw new \InvalidArgumentException("No loader found for the given storage type: $storageType");
    }
}

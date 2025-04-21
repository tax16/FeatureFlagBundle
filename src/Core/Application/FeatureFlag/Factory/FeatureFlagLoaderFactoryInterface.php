<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Loader\FeatureFlagLoaderInterface;

interface FeatureFlagLoaderFactoryInterface extends FeatureFlagLoaderInterface
{
    public function supports(string $storageType): bool;
}

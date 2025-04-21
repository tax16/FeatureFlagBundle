<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Loader;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;

interface FeatureFlagLoaderInterface
{
    /**
     * Charge les Feature Flags.
     *
     * @return FeatureFlag[]
     */
    public function loadFeatureFlags(): array;
}

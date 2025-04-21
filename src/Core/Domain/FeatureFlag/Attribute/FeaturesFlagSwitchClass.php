<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class FeaturesFlagSwitchClass
{
    /**
     * @param array<string>                      $features
     * @param array<string>                      $filteredMethod
     * @param array<FeatureFlagContextInterface> $context
     */
    public function __construct(
        public array $features,
        public string $switchedClass,
        public array $filteredMethod = [],
        public array $context = [],
    ) {
    }
}

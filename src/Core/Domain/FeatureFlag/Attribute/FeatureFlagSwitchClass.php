<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;

#[\Attribute(\Attribute::TARGET_CLASS)]
class FeatureFlagSwitchClass
{
    /**
     * @param array<string>                      $filteredMethod
     * @param array<FeatureFlagContextInterface> $context
     */
    public function __construct(
        public string $feature,
        public string $switchedClass,
        public array $filteredMethod = [],
        public array $context = [],
    ) {
    }
}

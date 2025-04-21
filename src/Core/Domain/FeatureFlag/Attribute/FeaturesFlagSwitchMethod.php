<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class FeaturesFlagSwitchMethod
{
    /**
     * @param array<string>                      $features
     * @param array<FeatureFlagContextInterface> $context
     */
    public function __construct(
        public array $features,
        public string $method,
        public array $context = [],
    ) {
    }
}

<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class FeatureFlagSwitchMethod
{
    /**
     * @param array<FeatureFlagContextInterface> $context
     */
    public function __construct(
        public string $feature,
        public string $method,
        public array $context = [],
    ) {
    }
}

<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception\FeatureFlagActiveException;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class IsFeatureInactive
{
    /**
     * @param array<string>                      $features
     * @param array<FeatureFlagContextInterface> $context
     */
    public function __construct(
        public array $features,
        public array $context = [],
        public string $exception = FeatureFlagActiveException::class
    ) {
    }
}

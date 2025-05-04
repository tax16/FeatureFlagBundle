<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Checker;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureInactive;

interface FeatureFlagStateAccessCheckerInterface
{
    public function check(IsFeatureInactive|IsFeatureActive $config): void;
}

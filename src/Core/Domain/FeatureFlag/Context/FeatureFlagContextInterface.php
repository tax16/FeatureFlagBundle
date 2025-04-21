<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context;

interface FeatureFlagContextInterface
{
    public function isAllowed(): bool;
}

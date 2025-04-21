<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;

interface FeatureFlagProviderInterface
{
    /**
     * @param array<FeatureFlagContextInterface>|null $context
     */
    public function provideStateByFlag(string $flag, ?array $context = null): bool;

    /**
     * @param string[]                                $flags
     * @param array<FeatureFlagContextInterface>|null $context
     */
    public function provideStateByFlags(array $flags, ?array $context = null): bool;
}

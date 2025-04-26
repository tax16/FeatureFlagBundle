<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider;

use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactory;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;

class FeatureFlagProvider implements FeatureFlagProviderInterface
{
    private FeatureFlagLoaderFactory $featureFlagLoaderFactory;
    /**
     * @var array<string, bool>
     */
    private static array $cachedFeatureStates = [];

    public function __construct(FeatureFlagLoaderFactory $featureFlagLoaderFactory)
    {
        $this->featureFlagLoaderFactory = $featureFlagLoaderFactory;
    }

    private function loadFeatureStates(): void
    {
        if (!empty(self::$cachedFeatureStates)) {
            return;
        }

        $loader = $this->featureFlagLoaderFactory->create();
        $featureFlags = $loader->loadFeatureFlags();

        self::$cachedFeatureStates = [];
        foreach ($featureFlags as $featureFlag) {
            self::$cachedFeatureStates[mb_strtolower($featureFlag->getName())] = $featureFlag->isEnabled();
        }
    }

    public function isFeatureActive(string $flag, ?array $context = null): bool
    {
        $this->loadFeatureStates();

        $flag = mb_strtolower($flag);

        if (!array_key_exists($flag, self::$cachedFeatureStates)) {
            throw new \InvalidArgumentException(sprintf('Feature flag "%s" does not exist.', $flag));
        }

        return self::$cachedFeatureStates[$flag];
    }

    /**
     * Check if all given feature flags are enabled.
     * {@inheritDoc}
     */
    public function isAllFeaturesActive(array $flags, ?array $context = null): bool
    {
        $this->loadFeatureStates();

        $flags = array_map('mb_strtolower', $flags);

        foreach ($flags as $flag) {
            if (!array_key_exists($flag, self::$cachedFeatureStates)) {
                throw new \InvalidArgumentException(sprintf('Feature flag "%s" does not exist.', $flag));
            }
            if (!self::$cachedFeatureStates[$flag]) {
                return false;
            }
        }

        return true;
    }

    public static function resetCache(): void
    {
        self::$cachedFeatureStates = [];
    }
}

<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\Decorator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Loader\FeatureFlagLoaderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\CacheInterface;

class FeatureFlagLoaderCacheDecorator implements FeatureFlagLoaderInterface
{
    private const CACHE_KEY = 'feature_flags';

    public function __construct(
        private readonly FeatureFlagLoaderInterface $loader,
        private readonly ApplicationLoggerInterface $logger,
        private readonly CacheInterface $cache,
        #[Autowire(param: 'feature_flags.storage.path')]
        private readonly bool $isActiveCache = true,
    ) {
    }

    public function loadFeatureFlags(): array
    {
        if (!$this->isActiveCache) {
            return $this->loader->loadFeatureFlags();
        }

        $this->logger->info(sprintf('Loading feature flags from %s', get_class($this->loader)));
        $data = $this->cache->get(self::CACHE_KEY);
        if ($data) {
            $this->logger->info('Loading feature flags from cache');

            return json_decode($data, true);
        }

        $featureFlags = $this->loader->loadFeatureFlags();
        $this->logger->info(sprintf('Loaded %d feature flags.', count($featureFlags)));
        $this->cache->set(self::CACHE_KEY, json_encode($featureFlags, JSON_THROW_ON_ERROR));

        return $featureFlags;
    }
}

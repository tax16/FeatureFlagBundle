<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\Decorator;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactoryInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\CacheInterface;

class FeatureFlagLoaderCacheDecorator implements FeatureFlagLoaderFactoryInterface
{
    private const CACHE_KEY = 'feature_flags';

    public function __construct(
        private readonly FeatureFlagLoaderFactoryInterface $loader,
        private readonly ApplicationLoggerInterface $logger,
        private readonly CacheInterface $cache,
        #[Autowire(param: 'feature_flags.cache')]
        private readonly bool $isActiveCache = true,
        #[Autowire(param: 'feature_flags.ttl')]
        private readonly int $ttl = 3600,
    ) {
    }

    /**
     * @return FeatureFlag[]
     */
    public function loadFeatureFlags(): array
    {
        if (!$this->isActiveCache) {
            return $this->loader->loadFeatureFlags();
        }

        $this->logger->info(sprintf('Loading feature flags from %s', get_class($this->loader)));

        $data = $this->cache->get(self::CACHE_KEY);

        if ($data) {
            $this->logger->info('Loading feature flags from cache');

            $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            return array_map([FeatureFlag::class, 'fromArray'], $decoded);
        }

        $featureFlags = $this->loader->loadFeatureFlags();
        $this->logger->info(sprintf('Loaded %d feature flags.', count($featureFlags)));

        $this->cache->set(
            self::CACHE_KEY,
            json_encode(array_map(static fn ($flag) => $flag->toArray(), $featureFlags), JSON_THROW_ON_ERROR),
            $this->ttl
        );

        return $featureFlags;
    }

    public function supports(string $storageType): bool
    {
        return $this->loader->supports($storageType);
    }
}

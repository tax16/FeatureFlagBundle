<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Tax16\FeatureFlagBundle\Core\Domain\Port\CacheInterface;

class CacheAdapter implements CacheInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return null;
        }

        return $item->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $item = $this->cache->getItem($key);

        $item->set($value);

        $item->expiresAfter($ttl);

        $this->cache->save($item);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(string $key): void
    {
        $this->cache->deleteItem($key);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }
}

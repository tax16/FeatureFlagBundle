<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\Port;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttl = 3600): void;

    public function delete(string $key): void;

    public function clear(): void;
}

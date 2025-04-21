<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\Port;

interface ConfigurationProviderInterface
{
    /**
     * @return array<mixed>|bool|float|int|string|null
     */
    public function get(string $key);
}

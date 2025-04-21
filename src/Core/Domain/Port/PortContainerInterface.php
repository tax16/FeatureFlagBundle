<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\Port;

interface PortContainerInterface
{
    public function get(string $id): ?object;
}

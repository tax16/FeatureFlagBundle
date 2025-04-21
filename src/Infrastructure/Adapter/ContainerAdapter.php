<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\Adapter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\PortContainerInterface;

readonly class ContainerAdapter implements PortContainerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function get(string $id): ?object
    {
        return $this->container->get($id);
    }
}

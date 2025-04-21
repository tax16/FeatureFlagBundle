<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository\FeatureFlagRepositoryInterface;

class DeleteFeatureFlagHandler
{
    private FeatureFlagRepositoryInterface $repository;

    public function __construct(FeatureFlagRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(string $flagName): void
    {
        $featureFlag = $this->repository->findByName($flagName);

        if (!$featureFlag) {
            throw new \RuntimeException("Feature flag '$flagName' not found.");
        }

        $this->repository->delete($featureFlag);
    }
}

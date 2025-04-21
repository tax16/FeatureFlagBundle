<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository\FeatureFlagRepositoryInterface;

class SaveFeatureFlagHandler
{
    private FeatureFlagRepositoryInterface $repository;

    public function __construct(FeatureFlagRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(
        string $flagName,
        ?bool $enabled = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): void {
        $featureFlag = $this->repository->findByName($flagName);

        if (!$featureFlag) {
            $featureFlag = new FeatureFlag($flagName, $enabled ?? false, $startDate, $endDate);
        } else {
            $featureFlag->update($enabled, $startDate, $endDate);
        }

        $this->repository->save($featureFlag);
    }
}

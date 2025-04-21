<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;

interface FeatureFlagRepositoryInterface
{
    public function save(FeatureFlag $featureFlag): void;

    public function findByName(string $name): ?FeatureFlag;

    /**
     * @param array<string> $names
     *
     * @return array<FeatureFlag>|null
     */
    public function findByNames(array $names): ?array;

    public function delete(FeatureFlag $featureFlag): void;

    /**
     * @return array<FeatureFlag>
     */
    public function findAll(): array;
}

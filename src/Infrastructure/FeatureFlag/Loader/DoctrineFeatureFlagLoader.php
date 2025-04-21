<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader;

use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactoryInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository\FeatureFlagRepositoryInterface;

class DoctrineFeatureFlagLoader implements FeatureFlagLoaderFactoryInterface
{
    private FeatureFlagRepositoryInterface $featureFlagRepository;

    public function __construct(FeatureFlagRepositoryInterface $featureFlagRepository)
    {
        $this->featureFlagRepository = $featureFlagRepository;
    }

    public function loadFeatureFlags(): array
    {
        return $this->featureFlagRepository->findAll();
    }

    public function supports(string $storageType): bool
    {
        return $storageType === FeatureFlagStorageType::DOCTRINE->value;
    }
}

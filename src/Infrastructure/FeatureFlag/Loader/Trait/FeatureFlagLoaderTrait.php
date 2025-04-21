<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\Trait;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;

trait FeatureFlagLoaderTrait
{
    /**
     * @param array<string, mixed> $data
     *
     * @return FeatureFlag[]
     *
     * @throws \DateMalformedStringException
     */
    protected function parseFeatureFlags(array $data): array
    {
        $featureFlags = [];
        foreach ($data as $flagData) {
            $startDate = !isset($flagData['start_date']) ? null : new \DateTime($flagData['start_date']);
            $endDate = !isset($flagData['end_date']) ? null : new \DateTime($flagData['end_date']);

            $featureFlags[] = new FeatureFlag(
                $flagData['name'],
                $flagData['enabled'],
                $startDate,
                $endDate
            );
        }

        return $featureFlags;
    }
}

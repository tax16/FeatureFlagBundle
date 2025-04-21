<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum;

enum FeatureFlagStorageType: string
{
    case DOCTRINE = 'doctrine';
    case JSON = 'json';
    case YAML = 'yaml';

    public function isEditable(): bool
    {
        return self::DOCTRINE === $this;
    }
}

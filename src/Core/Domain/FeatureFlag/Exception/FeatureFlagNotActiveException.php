<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception\Abstract\DomainException;

class FeatureFlagNotActiveException extends DomainException
{
    public function __construct(string $flagsString)
    {
        parent::__construct("Feature flags with name(s) {$flagsString} are not active.");
    }
}

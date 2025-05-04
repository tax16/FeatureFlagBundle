<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception\Abstract\DomainException;

class FeatureFlagActiveException extends DomainException
{
    public function __construct(string $flagsString)
    {
        parent::__construct("Feature flags with name(s) {$flagsString} are already active.");
    }
}

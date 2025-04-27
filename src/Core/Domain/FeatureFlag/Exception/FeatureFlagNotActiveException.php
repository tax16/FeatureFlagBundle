<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Exception\Abstract\DomainException;

class FeatureFlagNotActiveException extends DomainException
{
    /**
     * @param array<string> $flags
     */
    public function __construct(array $flags)
    {
        $flagsString = implode(', ', $flags);

        parent::__construct("Feature flags with name(s) {$flagsString} are not active.");
    }
}

<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;

class FakeClassSwitchMethodParamDiff
{
    #[FeatureFlagSwitchMethod(feature: 'new_feature', method: 'alternativeMethod')]
    public function execute(): string
    {
        return "Original Method";
    }

    public function alternativeMethod(int $fake): string
    {
        return "Switched Method";
    }
}
<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass\FakeClass;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;

#[FeatureFlagSwitchClass(feature: 'new_feature', switchedClass: FakeServiceSwitchedNoMethod::class)]
class FakeServiceSwitchNoMethod
{
    public function execute(): string
    {
        return "Original Method";
    }
}
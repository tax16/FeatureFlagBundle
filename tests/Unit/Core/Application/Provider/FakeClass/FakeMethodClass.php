<?php

namespace App\Tests\Unit\Core\Application\Provider\FakeClass;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchMethod;

#[FeatureFlagSwitchClass(feature: 'fake_feature', switchedClass: \stdClass::class)]
class FakeMethodClass
{
    #[FeatureFlagSwitchMethod(feature: 'm1', method: 'alternativeMethod')]
    public function singleFeatureMethod(): void {}

    #[FeaturesFlagSwitchMethod(features: ['f2', 'f3'], method: 'alt')]
    public function multiFeatureMethod(): void {}

    public function normalMethod(): void {}
}
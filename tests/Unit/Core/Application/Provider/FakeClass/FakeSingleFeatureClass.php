<?php

namespace App\Tests\Unit\Core\Application\Provider\FakeClass;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;

#[FeatureFlagSwitchClass(feature: 'fake_feature', switchedClass: \stdClass::class)]
class FakeSingleFeatureClass
{

}
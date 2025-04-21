<?php

namespace App\Tests\Unit\Core\Application\Provider\FakeClass;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;

#[FeaturesFlagSwitchClass(features: ['f1', 'f2'], switchedClass: \stdClass::class)]
class FakeMultipleFeaturesClass
{

}
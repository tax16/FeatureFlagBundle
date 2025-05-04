<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;


use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;

#[FeatureFlagSwitchClass(
    feature: 'test_feature',
    switchedClass: FakeSwitchedServiceWithIncompatibleMethods::class,
    filteredMethod: ['someMethod']
)]
class FakeServiceWithIncompatibleMethods
{
    public function someMethod(): string
    {
        return 'service method';
    }
}
<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;


use App\Tests\Unit\Core\Application\Proxy\FakeSwitchedService;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;

#[FeatureFlagSwitchClass(
    feature: 'test_feature',
    switchedClass: FakeSwitchedService::class,
    filteredMethod: ['someMethod']
)]
class FakeClassServiceWithFeature
{
    public function someMethod(): string
    {
        return 'service method';
    }
}
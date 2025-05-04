<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;


use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;

#[IsFeatureActive(
    features: ['test_feature'],
    context: [],
    exception: \RuntimeException::class
)]
class FakeServiceWithFeature
{
    public function someMethod(): string
    {
        return 'ok';
    }
}
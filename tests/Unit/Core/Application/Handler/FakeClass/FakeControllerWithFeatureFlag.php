<?php

namespace App\Tests\Unit\Core\Application\Handler\FakeClass;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchRoute;

class FakeControllerWithFeatureFlag
{
    #[FeaturesFlagSwitchRoute(
        features: ['feature_x', 'feature_y'],
        switchedRoute: '/maintenance',
        context: []
    )]
    public function test(): void {

    }
}
<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;

class FakeClassSwitchMethodWithMissingAlternative
{

    #[FeatureFlagSwitchMethod(feature: 'new_feature', method: 'alternativeMethod')]
    public function execute(): string
    {
        return "Original Method";
    }
}
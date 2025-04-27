<?php

namespace App\Tests\Unit\Core\Application\Provider;

use App\Tests\Unit\Core\Application\Provider\FakeClass\FakeMethodClass;
use App\Tests\Unit\Core\Application\Provider\FakeClass\FakeMultipleFeaturesClass;
use App\Tests\Unit\Core\Application\Provider\FakeClass\FakeSingleFeatureClass;
use Codeception\PHPUnit\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchMethod;

class FeatureFlagAttributeProviderTest extends TestCase
{
    public function test_provideClassAttributeConfig_with_single_feature_attribute(): void
    {
        $config = FeatureFlagAttributeProvider::provideClassAttributeConfig(FakeSingleFeatureClass::class);

        $this->assertInstanceOf(FeatureFlagSwitchClass::class, $config);
        $this->assertSame('fake_feature', $config->feature);
    }

    public function test_provideClassAttributeConfig_with_multiple_features_attribute(): void
    {
        $config = FeatureFlagAttributeProvider::provideClassAttributeConfig(FakeMultipleFeaturesClass::class);

        $this->assertInstanceOf(FeaturesFlagSwitchClass::class, $config);
        $this->assertSame(['f1', 'f2'], $config->features);
    }

    public function test_provideClassAttributeConfig_returns_null_when_no_attribute(): void
    {
        $config = FeatureFlagAttributeProvider::provideClassAttributeConfig(\stdClass::class);

        $this->assertNull($config);
    }

    public function test_provideMethodAttributeConfig_with_single_feature(): void
    {
        $method = new \ReflectionMethod(FakeMethodClass::class, 'singleFeatureMethod');
        $config = FeatureFlagAttributeProvider::provideMethodAttributeConfig($method);

        $this->assertInstanceOf(FeatureFlagSwitchMethod::class, $config);
        $this->assertSame('m1', $config->feature);
        $this->assertSame('alternativeMethod', $config->method);
    }

    public function test_provideMethodAttributeConfig_with_multiple_features(): void
    {
        $method = new \ReflectionMethod(FakeMethodClass::class, 'multiFeatureMethod');
        $config = FeatureFlagAttributeProvider::provideMethodAttributeConfig($method);

        $this->assertInstanceOf(FeaturesFlagSwitchMethod::class, $config);
        $this->assertSame(['f2', 'f3'], $config->features);
        $this->assertSame('alt', $config->method);
    }

    public function test_provideMethodAttributeConfig_returns_null_when_no_attribute(): void
    {
        $method = new \ReflectionMethod(FakeMethodClass::class, 'normalMethod');
        $this->assertNull(FeatureFlagAttributeProvider::provideMethodAttributeConfig($method));
    }
}
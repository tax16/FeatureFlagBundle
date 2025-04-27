<?php

namespace App\Tests\Unit\Core\Application\Provider;

use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactory;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Loader\FeatureFlagLoaderInterface;

class FeatureFlagProviderTest extends TestCase
{
    private $loaderFactoryMock;
    private $loaderMock;
    private $featureFlagProvider;

    protected function setUp(): void
    {
        $this->loaderFactoryMock = $this->createMock(FeatureFlagLoaderFactory::class);

        $this->loaderMock = $this->createMock(FeatureFlagLoaderInterface::class);

        $this->loaderFactoryMock->method('create')->willReturn($this->loaderMock);

        $this->featureFlagProvider = new FeatureFlagProvider($this->loaderFactoryMock);
    }

    protected function tearDown(): void
    {
        FeatureFlagProvider::resetCache();
    }

    public function testProvideStateByFlagReturnsTrueWhenFlagIsEnabled()
    {
        $flagName = 'test_flag';
        $featureFlag = $this->createFeatureFlag($flagName, true);

        $this->loaderMock->method('loadFeatureFlags')->willReturn([$featureFlag]);

        $this->assertTrue($this->featureFlagProvider->isFeatureActive($flagName));
        
    }

    public function testProvideStateByFlagReturnsFalseWhenFlagIsDisabled()
    {
        $flagName = 'test_flag';
        $featureFlag = $this->createFeatureFlag($flagName, false);

        $this->loaderMock->method('loadFeatureFlags')->willReturn([$featureFlag]);

        $this->assertFalse($this->featureFlagProvider->isFeatureActive($flagName));
        
    }

    public function testProvideStateByFlagThrowsExceptionForUnknownFlag()
    {
        $this->loaderMock->method('loadFeatureFlags')->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Feature flag "unknown_flag" does not exist.');

        $this->featureFlagProvider->isFeatureActive('unknown_flag');
    }

    public function testProvideStateByFlagsReturnsTrueWhenAllFlagsAreEnabled()
    {
        $flags = ['flag_one', 'flag_two'];
        $featureFlags = [
            $this->createFeatureFlag('flag_one', true),
            $this->createFeatureFlag('flag_two', true),
        ];

        $this->loaderMock->method('loadFeatureFlags')->willReturn($featureFlags);

        $this->assertTrue($this->featureFlagProvider->areAllFeaturesActive($flags));
        
    }

    public function testProvideStateByFlagsReturnsFalseWhenAtLeastOneFlagIsDisabled()
    {
        $flags = ['flag_one', 'flag_two'];
        $featureFlags = [
            $this->createFeatureFlag('flag_one', true),
            $this->createFeatureFlag('flag_two', false),
        ];

        $this->loaderMock->method('loadFeatureFlags')->willReturn($featureFlags);

        $this->assertFalse($this->featureFlagProvider->areAllFeaturesActive($flags));
        
    }

    public function testProvideStateByFlagsThrowsExceptionWhenAFlagIsMissing()
    {
        $this->loaderMock->method('loadFeatureFlags')->willReturn([
            $this->createFeatureFlag('existing_flag', true),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Feature flag "missing_flag" does not exist.');

        $this->featureFlagProvider->areAllFeaturesActive(['existing_flag', 'missing_flag']);
    }

    private function createFeatureFlag(string $name, bool $enabled)
    {
        $featureFlagMock = $this->createMock(FeatureFlag::class);
        $featureFlagMock->method('getName')->willReturn($name);
        $featureFlagMock->method('isEnabled')->willReturn($enabled);

        return $featureFlagMock;
    }
}
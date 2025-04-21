<?php

namespace App\Tests\Unit\Core\Application\Factory;

use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactory;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactoryInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ConfigurationProviderInterface;

class FeatureFlagLoaderFactoryTest extends TestCase
{
    public function testCreateReturnsCorrectLoader(): void
    {
        $parametersMock = $this->createMock(ConfigurationProviderInterface::class);
        $parametersMock->method('get')->with('feature_flags.storage.type')->willReturn('database');

        $loaderMock = $this->createMock(FeatureFlagLoaderFactoryInterface::class);
        $loaderMock->method('supports')->with('database')->willReturn(true);

        $unsupportedLoaderMock = $this->createMock(FeatureFlagLoaderFactoryInterface::class);
        $unsupportedLoaderMock->method('supports')->with('database')->willReturn(false);

        $factory = new FeatureFlagLoaderFactory($parametersMock, [$unsupportedLoaderMock, $loaderMock]);

        $this->assertSame($loaderMock, $factory->create());
    }

    public function testCreateThrowsExceptionWhenNoLoaderSupportsStorageType(): void
    {
        $parametersMock = $this->createMock(ConfigurationProviderInterface::class);
        $parametersMock->method('get')->with('feature_flags.storage.type')->willReturn('unknown_type');

        $loaderMock = $this->createMock(FeatureFlagLoaderFactoryInterface::class);
        $loaderMock->method('supports')->with('unknown_type')->willReturn(false);

        $factory = new FeatureFlagLoaderFactory($parametersMock, [$loaderMock]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No loader found for the given storage type: unknown_type");

        $factory->create();
    }
}
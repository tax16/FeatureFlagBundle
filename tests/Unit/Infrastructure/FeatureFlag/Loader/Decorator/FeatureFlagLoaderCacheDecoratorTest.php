<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\Loader\Decorator;

use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactoryInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\CacheInterface;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\Decorator\FeatureFlagLoaderCacheDecorator;

class FeatureFlagLoaderCacheDecoratorTest extends TestCase
{
    private $loaderMock;
    private $loggerMock;
    private $cacheMock;
    private $decorator;

    protected function setUp(): void
    {
        $this->loaderMock = $this->createMock(FeatureFlagLoaderFactoryInterface::class);
        $this->loggerMock = $this->createMock(ApplicationLoggerInterface::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);

        $this->decorator = new FeatureFlagLoaderCacheDecorator(
            $this->loaderMock,
            $this->loggerMock,
            $this->cacheMock
        );
    }

    public function testLoadFeatureFlagsReturnsCachedData()
    {
        $cachedData = json_encode([
            ['name' => 'feature_1', 'enabled' => true, 'startDate' => null, 'endDate' => null],
            ['name' => 'feature_2', 'enabled' => false, 'startDate' => null, 'endDate' => null],
        ], JSON_THROW_ON_ERROR);

        $this->cacheMock->method('get')->with('feature_flags')->willReturn($cachedData);

        $this->loggerMock->expects($this->exactly(2))->method('info');

        $featureFlags = $this->decorator->loadFeatureFlags();

        $this->assertCount(2, $featureFlags);
        $this->assertSame('feature_1', $featureFlags[0]->getName());
        $this->assertTrue($featureFlags[0]->isEnabled());
    }

    public function testLoadFeatureFlagsLoadsFromSourceAndCachesIt()
    {
        $featureFlags = [
            $this->createFeatureFlag('feature_1', true),
            $this->createFeatureFlag('feature_2', false),
        ];

        $this->cacheMock->method('get')->with('feature_flags')->willReturn(null);

        $this->loaderMock->method('loadFeatureFlags')->willReturn($featureFlags);

        $this->loggerMock->expects($this->exactly(2))->method('info');

        $this->cacheMock->expects($this->once())->method('set')
            ->with('feature_flags',json_encode(array_map(static fn ($flag) => $flag->toArray(), $featureFlags), JSON_THROW_ON_ERROR));

        $result = $this->decorator->loadFeatureFlags();

        $this->assertCount(2, $result);
        $this->assertInstanceOf(FeatureFlag::class, $result[0]);
        $this->assertInstanceOf(FeatureFlag::class, $result[1]);
    }

    private function createFeatureFlag(string $name, bool $enabled)
    {
        $featureFlagMock = $this->createMock(FeatureFlag::class);
        $featureFlagMock->method('getName')->willReturn($name);
        $featureFlagMock->method('isEnabled')->willReturn($enabled);

        return $featureFlagMock;
    }

    private function testLoadFeatureWithoutCache()
    {
        $this->decorator = new FeatureFlagLoaderCacheDecorator(
            $this->loaderMock,
            $this->loggerMock,
            $this->cacheMock,
            false
        );
        $this->cacheMock->expects($this->never())->method('get')->with('feature_flags')->willReturn(null);
        $this->loaderMock->method('loadFeatureFlags')->willReturn([]);
        $this->decorator->loadFeatureFlags();
    }
}
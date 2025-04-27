<?php

namespace App\Tests\Unit\Core\Application\Proxy;

use App\Tests\Unit\Core\Application\Proxy\FakeClass\FakeServiceWithFeature;
use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory\StatusCheckProxyFactory;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ProxyInterceptorInterface;

class StatusCheckProxyFactoryTest  extends TestCase
{
    private StatusCheckProxyFactory $factory;
    private ApplicationLoggerInterface $logger;
    private FeatureFlagProviderInterface $featureFlagProvider;
    private ProxyInterceptorInterface $proxyInterceptor;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(ApplicationLoggerInterface::class);
        $this->featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $this->proxyInterceptor = $this->createMock(ProxyInterceptorInterface::class);

        $this->factory = new StatusCheckProxyFactory(
            $this->logger,
            $this->featureFlagProvider,
            $this->proxyInterceptor,
        );
    }

    public function testCreateByClassWithoutFeatureConfigReturnsOriginalService(): void
    {
        $service = new \stdClass();

        $proxy = $this->factory->createByClass($service);

        $this->assertSame($service, $proxy);
    }


    public function testCreateByClassWithInvalidFeatureThrowsExceptionAndReturnsProxy()
    {
        $service = new FakeServiceWithFeature();

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(false);

        $this->proxyInterceptor
            ->method('createProxy')
            ->willReturn(new \stdClass());

        $proxy = $this->factory->createByClass($service);

        $this->assertInstanceOf(\stdClass::class, $proxy);
    }

    public function testCreateByMethodReturnsProxy()
    {
        $service = new FakeServiceWithFeature();

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(true);

        $this->proxyInterceptor
            ->method('createProxy')
            ->willReturn(new \stdClass());

        $proxy = $this->factory->createByMethod($service);

        $this->assertInstanceOf(\stdClass::class, $proxy);
    }

    public function testValidateFeatureWithActiveFeature()
    {
        $config = new IsFeatureActive(
            features: ['test_feature'],
            context: [],
            exception: \RuntimeException::class
        );

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(true);

        $this->factory->validateFeature($config);

        $this->expectNotToPerformAssertions();
    }

    public function testValidateFeatureWithInactiveFeatureThrows()
    {
        $this->expectException(\RuntimeException::class);

        $config = new IsFeatureActive(
            features: ['test_feature'],
            context: [],
            exception: \RuntimeException::class
        );

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(false);

        $this->factory->validateFeature($config);
    }
}
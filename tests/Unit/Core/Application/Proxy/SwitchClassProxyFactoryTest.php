<?php

namespace App\Tests\Unit\Core\Application\Proxy;

use App\Tests\Unit\Core\Application\Proxy\FakeClass\FakeClassServiceWithFeature;
use App\Tests\Unit\Core\Application\Proxy\FakeClass\FakeIncompatibleSwitchedService;
use App\Tests\Unit\Core\Application\Proxy\FakeClass\FakeServiceWithFeature;
use App\Tests\Unit\Core\Application\Proxy\FakeClass\FakeServiceWithoutFeature;
use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory\SwitchClassProxyFactory;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ProxyInterceptorInterface;

class SwitchClassProxyFactoryTest extends TestCase
{
    private SwitchClassProxyFactory $factory;
    private ApplicationLoggerInterface $logger;
    private FeatureFlagProviderInterface $featureFlagProvider;
    private ProxyInterceptorInterface $proxyInterceptor;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(ApplicationLoggerInterface::class);
        $this->featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $this->proxyInterceptor = $this->createMock(ProxyInterceptorInterface::class);

        $this->factory = new SwitchClassProxyFactory(
            $this->logger,
            $this->featureFlagProvider,
            $this->proxyInterceptor
        );
    }

    public function testCreateProxyWithoutFeatureConfigReturnsService()
    {
        $service = new FakeServiceWithoutFeature();
        $switchedService = new FakeSwitchedService();

        $this->proxyInterceptor
            ->method('createProxy')
            ->willReturn($service);

        $result = $this->factory->createProxy($service, $switchedService);

        $this->assertSame($service, $result);
    }

    public function testCreateProxyWithActiveFeatureReturnsSwitchedService()
    {
        $service = new FakeServiceWithFeature();
        $switchedService = new FakeSwitchedService();

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(true);

        $this->proxyInterceptor
            ->method('createProxy')
            ->willReturn($switchedService);

        $result = $this->factory->createProxy($service, $switchedService);

        $this->assertSame($switchedService, $result);
    }

    public function testCreateProxyWithInactiveFeatureReturnsOriginalService()
    {
        $service = new FakeServiceWithFeature();
        $switchedService = new FakeSwitchedService();

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(false);

        $this->proxyInterceptor
            ->method('createProxy')
            ->willReturn($service);

        $result = $this->factory->createProxy($service, $switchedService);

        $this->assertSame($service, $result);
    }


    public function testBuildFlagInterceptorsWithValidMethodSwitching()
    {
        $service = new FakeServiceWithFeature();
        $switchedService = new FakeSwitchedService();

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(true);

        $this->proxyInterceptor
            ->method('createProxy')
            ->willReturn($switchedService);

        $result = $this->factory->createProxy($service, $switchedService);

        $this->assertSame($switchedService, $result);
    }
}
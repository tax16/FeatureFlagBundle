<?php

namespace App\Tests\Unit\Core\Application\Proxy;

use App\Tests\Unit\Core\Application\Proxy\FakeClass\FakeServiceWithFeature;
use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory\StatusCheckProxyFactory;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Checker\FeatureFlagStateAccessCheckerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ProxyInterceptorInterface;

class StatusCheckProxyFactoryTest extends TestCase
{
    private StatusCheckProxyFactory $factory;
    private ApplicationLoggerInterface $logger;
    private FeatureFlagStateAccessCheckerInterface $accessChecker;
    private ProxyInterceptorInterface $proxyInterceptor;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(ApplicationLoggerInterface::class);
        $this->accessChecker = $this->createMock(FeatureFlagStateAccessCheckerInterface::class);
        $this->proxyInterceptor = $this->createMock(ProxyInterceptorInterface::class);

        $this->factory = new StatusCheckProxyFactory(
            $this->logger,
            $this->proxyInterceptor,
            $this->accessChecker
        );
    }

    public function testCreateByClassWithoutFeatureConfigReturnsOriginalService(): void
    {
        $service = new \stdClass();

        $result = $this->factory->createByClass($service);

        $this->assertSame($service, $result);
    }

    public function testCreateByClassWithInvalidFeatureThrowsExceptionAndReturnsProxy(): void
    {
        $service = new FakeServiceWithFeature();

        $this->accessChecker
            ->method('check')
            ->willThrowException(new \RuntimeException('Feature disabled'));

        $this->proxyInterceptor
            ->expects($this->once())
            ->method('createProxy')
            ->willReturn(new \stdClass());

        $result = $this->factory->createByClass($service);

        $this->assertInstanceOf(\stdClass::class, $result);
    }

    public function testCreateByMethodReturnsProxy(): void
    {
        $service = new FakeServiceWithFeature();

        $this->accessChecker
            ->method('check');

        $this->proxyInterceptor
            ->expects($this->once())
            ->method('createProxy')
            ->willReturn(new \stdClass());

        $result = $this->factory->createByMethod($service);

        $this->assertInstanceOf(\stdClass::class, $result); // Devrait renvoyer un proxy
    }
}
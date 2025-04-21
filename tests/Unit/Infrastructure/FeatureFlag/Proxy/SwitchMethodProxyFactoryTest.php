<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\Proxy;

use App\Tests\Unit\Infrastructure\FeatureFlag\Proxy\FakeClass\FakeClassOne;
use App\Tests\Unit\Infrastructure\FeatureFlag\Proxy\FakeClass\FakeClassSwitchMethod;
use App\Tests\Unit\Infrastructure\FeatureFlag\Proxy\FakeClass\FakeClassSwitchMethodParamDiff;
use App\Tests\Unit\Infrastructure\FeatureFlag\Proxy\FakeClass\FakeClassSwitchMethodWithMissingAlternative;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagProvider;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Proxy\SwitchMethodProxyFactory;

class SwitchMethodProxyFactoryTest extends TestCase
{
    private ApplicationLoggerInterface $logger;
    private FeatureFlagProvider $featureFlagProvider;
    private SwitchMethodProxyFactory $proxyFactory;

    protected function tearDown(): void
    {
        FeatureFlagProvider::resetCache();
    }

    protected function setUp(): void
    {
        $this->logger = $this->createMock(ApplicationLoggerInterface::class);
        $this->featureFlagProvider = $this->createMock(FeatureFlagProvider::class);

        $this->proxyFactory = new SwitchMethodProxyFactory(
            $this->logger,
            $this->featureFlagProvider
        );
    }

    public function testExecuteMethodWithoutAttributeCallsOriginalMethod(): void
    {
        $proxy = $this->proxyFactory->createProxy(new FakeClassOne());
        $result = $proxy->execute();

        $this->assertSame("Original Method", $result);
    }

    public function testExecuteMethodSwitchesWhenFeatureFlagIsActive(): void
    {
        $service = new FakeClassSwitchMethod();

        $this->featureFlagProvider
            ->method('provideStateByFlags')
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('info');

        $proxy = $this->proxyFactory->createProxy($service);
        $result = $proxy->execute();
        $this->assertSame("Switched Method", $result);
    }

    public function testExecuteMethodDoesNotSwitchWhenFeatureFlagIsInactive(): void
    {
        $service = new FakeClassSwitchMethod();

        $this->featureFlagProvider
            ->method('provideStateByFlags')
            ->willReturn(false);

        $this->logger
            ->expects($this->never())
            ->method('info');

        $proxy = $this->proxyFactory->createProxy($service);
        $result = $proxy->execute();

        $this->assertSame("Original Method", $result);
    }

    public function testExecuteMethodThrowsExceptionWhenParametersAreIncompatible(): void
    {
        $service = new FakeClassSwitchMethodParamDiff();

        $this->featureFlagProvider
            ->method('provideStateByFlags')
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('info');

        /** @var FakeClassSwitchMethodParamDiff $proxy */
        $proxy = $this->proxyFactory->createProxy($service);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Parameters of method 'execute' and 'alternativeMethod' are not compatible");

        $proxy->execute();
    }

    public function testExecuteMethodThrowsExceptionWhenAlternativeMethodDoesNotExist(): void
    {
        $service = new FakeClassSwitchMethodWithMissingAlternative();

        $this->featureFlagProvider
            ->method('provideStateByFlags')
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('info');

        /** @var FakeClassSwitchMethodWithMissingAlternative $proxy */
        $proxy = $this->proxyFactory->createProxy($service);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("The method 'alternativeMethod' does not exist on App\Tests\Unit\Infrastructure\FeatureFlag\Proxy\FakeClass\FakeClassSwitchMethodWithMissingAlternative");

        $proxy->execute();
    }
}
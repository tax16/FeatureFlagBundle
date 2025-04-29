<?php

namespace App\Tests\Unit\Core\Application\Handler;

use App\Tests\Unit\Core\Application\Handler\FakeClass\FakeControllerWithFeatureFlag;
use App\Tests\Unit\Core\Application\Handler\FakeClass\FakeControllerWithoutFeatureFlag;
use Codeception\PHPUnit\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler\FeatureFlagSwitchRouteRouteHandler;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;

class FeatureFlagSwitchRouteRouteHandlerTest extends TestCase
{
    private FeatureFlagProviderInterface $featureFlagProvider;
    private ApplicationLoggerInterface $logger;
    private FeatureFlagSwitchRouteRouteHandler $handler;

    protected function setUp(): void
    {
        $this->featureFlagProvider = $this->createMock(FeatureFlagProviderInterface::class);
        $this->logger = $this->createMock(ApplicationLoggerInterface::class);
        $this->handler = new FeatureFlagSwitchRouteRouteHandler(
            $this->featureFlagProvider,
            $this->logger
        );
    }

    public function testHandleReturnsNullWhenNoFeatureFlagConfig(): void
    {
        $controller = new FakeControllerWithoutFeatureFlag();

        $result = $this->handler->handle($controller, $controller::class.'::test');

        $this->assertNull($result);
    }

    public function testHandleReturnsSwitchedRouteWhenFeaturesDisabled(): void
    {
        $controller = new FakeControllerWithFeatureFlag();

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(false);
        $this->logger->expects($this->once())
            ->method('info')
            ->with("Features 'feature_x,feature_y' are active for controller " . get_class($controller));

        $result = $this->handler->handle($controller, $controller::class.'::test');

        $this->assertNull($result);
    }

    public function testHandleReturnsNullWhenFeaturesEnabled(): void
    {
        $controller = new FakeControllerWithFeatureFlag();

        $this->featureFlagProvider
            ->method('areAllFeaturesActive')
            ->willReturn(true);


        $this->logger->expects($this->once())
            ->method('warning')
            ->with("Feature 'feature_x,feature_y' is disabled for controller " . get_class($controller));

        $result = $this->handler->handle($controller, $controller::class.'::test');
        $this->assertEquals('/maintenance', $result);
    }
}
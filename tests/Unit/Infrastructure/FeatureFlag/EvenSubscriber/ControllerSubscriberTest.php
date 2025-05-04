<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\EvenSubscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler\ControllerRouteHandlerInterface;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\EventSubscriber\ControllerSubscriber;

class ControllerSubscriberTest extends TestCase
{
    private ControllerRouteHandlerInterface $controllerEventHandler;
    private RouterInterface $router;

    protected function setUp(): void
    {
        $this->controllerEventHandler = $this->createMock(ControllerRouteHandlerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
    }

    public function testGetSubscribedEvents()
    {
        $events = ControllerSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::CONTROLLER, $events);
        $this->assertEquals('onController', $events[KernelEvents::CONTROLLER]);
    }

    private function createControllerEvent(
        Kernel $kernel,
        object $controller,
        $subRequest = HttpKernelInterface::MAIN_REQUEST,
    ): ControllerEvent {
        $request = new Request();

        return new ControllerEvent($kernel, [$controller, 'someMethod'], $request, $subRequest);
    }

    public function testShouldReturnDirectelyIfCheckDisabled()
    {
        $controller = new class {
            public function someMethod() {}
        };

        $subscriber = new ControllerSubscriber($this->controllerEventHandler, $this->router, false);
        $subscriber->onController($this->createControllerEvent($this->createMock(Kernel::class), $controller));
    }

    public function testShouldReturnDirectelyIfItIsNotMainRequest()
    {
        $controller = new class {
            public function someMethod() {}
        };

        $subscriber = new ControllerSubscriber($this->controllerEventHandler, $this->router, true);
        $subscriber->onController($this->createControllerEvent($this->createMock(Kernel::class), $controller, HttpKernelInterface::SUB_REQUEST));
    }

    public function testShouldReturnDirectelyIfItIsErrorController()
    {
        $http = $this->createMock(HttpKernelInterface::class);
        $error = $this->createMock(ErrorRendererInterface::class);

        $controller = new class($http, $error) extends ErrorController {
            public function __construct($http, $error)
            {
                parent::__construct($http, null, $error);
            }
            public function someMethod() {}
        };

        $subscriber = new ControllerSubscriber($this->controllerEventHandler, $this->router, true);
        $subscriber->onController($this->createControllerEvent($this->createMock(Kernel::class), $controller));
    }
}
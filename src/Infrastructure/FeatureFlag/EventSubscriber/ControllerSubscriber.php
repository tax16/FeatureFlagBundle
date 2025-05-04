<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler\ControllerRouteHandlerInterface;

readonly class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ControllerRouteHandlerInterface $controllerEventHandler,
        private RouterInterface $router,
        #[Autowire(param: 'feature_flags.controller_check')]
        private bool $checkControllerEnabled = false,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onController',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        if (!$this->checkControllerEnabled) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof ErrorController) {
            return;
        }

        $method = $event->getRequest()->attributes->get('_controller');
        $methodName = is_array($method) ? $method[1] : $method;

        $redirectPath = $this->controllerEventHandler->handle($controller, $methodName);
        if (!$redirectPath) {
            return;
        }

        $currentParams = $event->getRequest()->attributes->get('_route_params');

        $url = $this->router->generate($redirectPath, $currentParams);

        $event->setController(fn () => new RedirectResponse($url));
    }
}

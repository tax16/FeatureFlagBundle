<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler\ControllerRouteHandlerInterface;

readonly class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ControllerRouteHandlerInterface $controllerEventHandler,
        private RouterInterface $router,
        private HttpKernelInterface $httpKernel,
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
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof ErrorController) {
            return;
        }

        $request = $event->getRequest();
        $controllerAttr = $request->attributes->get('_controller');

        $methodName = is_array($controllerAttr) ? $controllerAttr[1] : $controllerAttr;
        if (!$methodName) {
            throw new BadRequestHttpException('Could not resolve method name from controller.');
        }

        $redirectRouteName = $this->controllerEventHandler->handle($controller, $methodName);
        if (!$redirectRouteName) {
            return;
        }

        $routeDefinition = $this->router->getRouteCollection()->get($redirectRouteName);
        if (!$routeDefinition) {
            throw new RouteNotFoundException($redirectRouteName);
        }

        $routeParams = $request->attributes->get('_route_params', []);
        $queryParams = $request->query->all();
        $postParams  = $request->request->all();

        $expectedParams = array_keys($routeDefinition->compile()->getPathVariables());
        $missing = array_diff($expectedParams, array_keys($routeParams));
        if (!empty($missing)) {
            throw new \InvalidArgumentException('Missing route parameters for redirection: '.implode(', ', $missing));
        }

        $subRequest = $request->duplicate(
            $queryParams,
            $postParams,
            array_merge(['_route' => $redirectRouteName], $routeParams) // attributes
        );

        $event->setController(fn () => $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST));
    }
}

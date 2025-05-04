<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler;

interface ControllerRouteHandlerInterface
{
    public function handle(object $controller, string $methodName): ?string;
}

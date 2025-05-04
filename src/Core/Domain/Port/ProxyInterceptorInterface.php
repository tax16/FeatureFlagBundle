<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\Port;

interface ProxyInterceptorInterface
{
    /**
     * @param array<\Closure> $interceptors
     */
    public function createProxy(object $service, array $interceptors): object;
}

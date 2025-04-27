<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\Adapter;

use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ProxyInterceptorInterface;

class ProxyInterceptorAdapter implements ProxyInterceptorInterface
{
    private AccessInterceptorValueHolderFactory $proxyHolder;

    public function __construct(
    ) {
        $this->proxyHolder = new AccessInterceptorValueHolderFactory();
    }

    public function createProxy(object $service, array $interceptors): object
    {
        return $this->proxyHolder->createProxy($service, $interceptors);
    }
}

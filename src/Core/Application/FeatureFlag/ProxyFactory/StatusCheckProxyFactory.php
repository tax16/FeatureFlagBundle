<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory;

use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Checker\FeatureFlagStateAccessCheckerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ProxyInterceptorInterface;

readonly class StatusCheckProxyFactory
{
    public function __construct(
        private ApplicationLoggerInterface $logger,
        private ProxyInterceptorInterface $proxyInterceptor,
        private FeatureFlagStateAccessCheckerInterface $featureFlagStateChecker,
    ) {
    }

    public function createByClass(object $service): object
    {
        $this->logger->info('Check State of Feature(s) to the class: '.$service::class);
        $config = FeatureFlagAttributeProvider::provideClassStatusAttributeConfig($service);

        if (!$config) {
            return $service;
        }

        try {
            $this->featureFlagStateChecker->check($config);
        } catch (\Throwable $exception) {
            $this->logger->info('Feature not enable for this class: '.$service::class);

            $interceptors = $this->buildClassInterceptors($service, $exception);

            return $this->proxyInterceptor->createProxy($service, $interceptors);
        }

        return $service;
    }

    public function createByMethod(object $service): object
    {
        $interceptors = $this->buildFlagInterceptors($service);

        return $this->proxyInterceptor->createProxy($service, $interceptors);
    }

    /**
     * @return array<string, \Closure>
     */
    private function buildClassInterceptors(object $service, \Throwable $exception): array
    {
        $this->logger->info('Disable all call on the class: '.$service::class);

        $reflection = new \ReflectionClass($service);
        $interceptors = [];

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $originalMethodName = $method->getName();
            $interceptors[$originalMethodName] = static function (
                object $proxy,
                object $instance,
                string $method,
                array $params,
                bool &$returnEarly,
            ) use ($exception) {
                $returnEarly = true;
                throw $exception;
            };
        }

        return $interceptors;
    }

    /**
     * @return array<string, \Closure>
     */
    private function buildFlagInterceptors(object $service): array
    {
        $this->logger->info('Check State of Feature(s) to the method of class: '.$service::class);

        $reflection = new \ReflectionClass($service);
        $interceptors = [];

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $config = FeatureFlagAttributeProvider::provideMethodStatusAttributeConfig($method);
            if (!$config) {
                continue;
            }

            $originalMethodName = $method->getName();

            $this->logger->info('Check feature of Method: '.$originalMethodName);

            $interceptors[$originalMethodName] = function (
                object $proxy,
                object $instance,
                string $method,
                array $params,
                bool &$returnEarly,
            ) use ($config, $originalMethodName) {
                $this->featureFlagStateChecker->check($config);

                $returnEarly = true;

                return $instance->$originalMethodName(...$params);
            };
        }

        return $interceptors;
    }
}

<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\ProxyFactory;

use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureInactive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;

readonly class FeatureFlagStatusProxyFactory
{
    public function __construct(
        private ApplicationLoggerInterface   $logger,
        private FeatureFlagProviderInterface $featureFlagProvider,
    )
    {
    }

    public function createByClass(object $service): object
    {
        $this->logger->info('Check State of Feature(s) to the class: ' . $service::class);
        $config = FeatureFlagAttributeProvider::provideClassStatusAttributeConfig($service);

        if (!$config) {
            return $service;
        }

        $this->validateFeature($config);

        return $service;
    }

    public function createByMethod(object $service): object
    {
        $factory = new AccessInterceptorValueHolderFactory();
        $interceptors = $this->buildMethodFlagInterceptors($service);

        return $factory->createProxy($service, $interceptors);
    }

    private function buildMethodFlagInterceptors(object $service): array
    {
        $this->logger->info('Check State of Feature(s) to the method of class: ' . $service::class);

        $reflection = new \ReflectionClass($service);
        $interceptors = [];

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $config = FeatureFlagAttributeProvider::provideMethodStatusAttributeConfig($method);
            if (!$config) {
                continue;
            }

            $originalMethodName = $method->getName();

            $this->logger->info('Check feature of Method: ' . $originalMethodName);

            $interceptors[$originalMethodName] = function (
                object $proxy,
                object $instance,
                string $method,
                array $params,
                bool &$returnEarly,
            ) use ($config, $originalMethodName) {
                $this->validateFeature($config);

                $returnEarly = true;
                return $instance->$originalMethodName(...$params);
            };
        }

        return $interceptors;
    }

    /**
     * @param IsFeatureInactive|IsFeatureActive $config
     * @return void
     */
    public function validateFeature(IsFeatureInactive|IsFeatureActive $config): void
    {
        $features = $config->features;
        $exception = $config->exception;
        $isFeatureActivate = $this->featureFlagProvider->isAllFeaturesActive($features, $config->context);

        if (
            ($config instanceof IsFeatureActive && !$isFeatureActivate)
            || ($config instanceof IsFeatureInactive && $isFeatureActivate)
        ) {
            $this->logger->error('State of Feature(s) is not compliant to the class');
            if (!class_exists($exception)) {
                throw new \InvalidArgumentException('invalid class exception provide');
            }

            throw new $exception($features);
        }
    }
}
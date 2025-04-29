<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory;

use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureInactive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ProxyInterceptorInterface;

readonly class StatusCheckProxyFactory
{
    public function __construct(
        private ApplicationLoggerInterface $logger,
        private FeatureFlagProviderInterface $featureFlagProvider,
        private ProxyInterceptorInterface $proxyInterceptor,
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
            $this->validateFeature($config);
        } catch (\Exception $exception) {
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
    private function buildClassInterceptors(object $service, \Exception $exception): array
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
                $this->validateFeature($config);

                $returnEarly = true;

                return $instance->$originalMethodName(...$params);
            };
        }

        return $interceptors;
    }

    /**
     * @throws \Throwable
     */
    public function validateFeature(IsFeatureInactive|IsFeatureActive $config): void
    {
        $features = $config->features;
        $exception = $config->exception;
        $isFeatureActivate = $this->featureFlagProvider->areAllFeaturesActive($features, $config->context);

        if (
            ($config instanceof IsFeatureActive && !$isFeatureActivate)
            || ($config instanceof IsFeatureInactive && $isFeatureActivate)
        ) {
            $this->logger->error('State of Feature(s) is not compliant to the class');
            if (!class_exists($exception)) {
                throw new \InvalidArgumentException('Invalid class exception provided');
            }

            $reflection = new \ReflectionClass($exception);

            $constructor = $reflection->getConstructor();
            if ($constructor && $constructor->getNumberOfParameters() > 0) {
                $this->checkAndThrowException($exception, $features);
            } else {
                // @phpstan-ignore-next-line
                throw new $exception();
            }
        }
    }

    /**
     * @param array<string> $features
     *
     * @throws \Throwable
     */
    private function checkAndThrowException(string $exception, array $features): void
    {
        if (is_a($exception, \Throwable::class, true)) {
            throw new $exception(implode(',', $features));
        }

        throw new \InvalidArgumentException("Class $exception must be a Throwable.");
    }
}

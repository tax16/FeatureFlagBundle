<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\ProxyFactory;

use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Checker\ClassChecker;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchMethod;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;

readonly class SwitchMethodProxyFactory
{
    public function __construct(
        private ApplicationLoggerInterface $logger,
        private FeatureFlagProviderInterface $featureFlagProvider,
    ) {
    }

    public function createProxy(object $service): object
    {
        $factory = new AccessInterceptorValueHolderFactory();
        $interceptors = $this->buildFlagInterceptors($service);

        return $factory->createProxy($service, $interceptors);
    }

    /**
     * @param FeaturesFlagSwitchMethod|FeatureFlagSwitchMethod $config
     *
     * @return string[]
     */
    private function extractFeatureNames(mixed $config): array
    {
        if ($config instanceof FeaturesFlagSwitchMethod) {
            return $config->features;
        }

        if ($config instanceof FeatureFlagSwitchMethod) {
            return [$config->feature];
        }

        throw new \InvalidArgumentException('Invalid config provided');
    }

    /**
     * @return array<string, \Closure>
     *
     * @throws \ReflectionException
     */
    private function buildFlagInterceptors(object $service): array
    {
        $reflection = new \ReflectionClass($service);
        $interceptors = [];

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            /** @var FeatureFlagSwitchMethod|FeaturesFlagSwitchMethod|null $config */
            $config = FeatureFlagAttributeProvider::provideMethodAttributeConfig($method);
            if (!$config) {
                continue;
            }

            $features = $this->extractFeatureNames($config);
            $alternativeMethod = $config->method;

            $originalMethodName = $method->getName();
            $interceptors[$originalMethodName] = function (
                object $proxy,
                object $instance,
                string $method,
                array $params,
                bool &$returnEarly,
            ) use ($features, $alternativeMethod, $originalMethodName, $service, $config) {
                if ($this->featureFlagProvider->isAllFeaturesActive($features, $config->context)) {
                    $featuresToString = implode(', ', $features);
                    $this->logger->info("Switching method '$originalMethodName' to '$alternativeMethod' (features '$featuresToString' is active)");

                    if (!method_exists($instance, $alternativeMethod)) {
                        $this->logger->error("The method '$alternativeMethod' does not exist on ".get_class($instance));

                        throw new \BadMethodCallException("The method '$alternativeMethod' does not exist on ".get_class($instance));
                    }

                    $original = new \ReflectionMethod($service, $originalMethodName);
                    $alt = new \ReflectionMethod($service, $alternativeMethod);

                    if (!ClassChecker::areParametersCompatible($original, $alt)) {
                        $this->logger->error("Parameters of method '$originalMethodName' and '$alternativeMethod' are not compatible.");

                        throw new \InvalidArgumentException("Parameters of method '$originalMethodName' and '$alternativeMethod' are not compatible.");
                    }

                    $returnEarly = true;

                    return $instance->$alternativeMethod(...$params);
                }

                return null;
            };
        }

        return $interceptors;
    }
}

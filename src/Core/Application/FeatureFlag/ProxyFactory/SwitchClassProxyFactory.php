<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\ProxyFactory;

use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Checker\ClassChecker;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ProxyInterceptorInterface;

readonly class SwitchClassProxyFactory
{
    public function __construct(
        private ApplicationLoggerInterface $logger,
        private FeatureFlagProviderInterface $featureFlagProvider,
        private ProxyInterceptorInterface $proxyInterceptor,
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public function createProxy(object $service, object $switchedService): object
    {
        $interceptors = $this->buildFlagInterceptors($service, $switchedService);

        return $this->proxyInterceptor->createProxy($service, $interceptors);
    }

    /**
     * @param FeatureFlagSwitchClass|FeaturesFlagSwitchClass $config
     *
     * @return string[]
     */
    private function extractFeatureNames(mixed $config): array
    {
        if ($config instanceof FeaturesFlagSwitchClass) {
            return $config->features;
        }

        if ($config instanceof FeatureFlagSwitchClass) {
            return [$config->feature];
        }

        throw new \InvalidArgumentException('Invalid config provided');
    }

    /**
     * @return array<string, \Closure>
     *
     * @throws \ReflectionException
     */
    private function buildFlagInterceptors(object $service, object $switchedService): array
    {
        /** @var FeatureFlagSwitchClass|FeaturesFlagSwitchClass|null $config */
        $config = FeatureFlagAttributeProvider::provideClassAttributeConfig($service);

        if (!$config) {
            return [];
        }

        if (!ClassChecker::areMethodsCompatible($service::class, $config->switchedClass, $config->filteredMethod)) {
            throw new \LogicException('The public methods of the original class and the switched class are not compatible.');
        }

        $features = $this->extractFeatureNames($config);
        $featuresToString = implode(', ', $features);

        $isFeatureActivate = $this->featureFlagProvider->areAllFeaturesActive($features, $config->context);
        $interceptors = [];

        $reflection = new \ReflectionClass($service);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ('__construct' === $method->getName()) {
                continue;
            }

            if (!empty($config->filteredMethod) && !in_array($method->getName(), $config->filteredMethod)) {
                continue;
            }

            $methodName = $method->getName();

            $interceptors[$methodName] = function (
                object $proxy,
                object $instance,
                string $calledMethod,
                array $params,
                bool &$returnEarly,
            ) use ($isFeatureActivate, $methodName, $switchedService, $featuresToString): mixed {
                $returnEarly = true;
                if ($isFeatureActivate) {
                    $this->logger->info("Switching method '$methodName' to '$methodName' (feature '$featuresToString' is active)");

                    return $switchedService->$methodName(...$params);
                }

                return $instance->$methodName(...$params);
            };
        }

        return $interceptors;
    }
}

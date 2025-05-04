<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler;

use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchRoute;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Checker\FeatureFlagStateAccessCheckerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;

readonly class FeatureFlagControllerRouteHandler implements ControllerRouteHandlerInterface
{
    public function __construct(
        private FeatureFlagProviderInterface $featureFlagProvider,
        private ApplicationLoggerInterface $logger,
        private FeatureFlagStateAccessCheckerInterface $featureFlagStateChecker,
    ) {
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function handle(object $controller, string $methodName): ?string
    {
        $reflectionMethod = new \ReflectionMethod($methodName);

        if ($featureFlagConfig = FeatureFlagAttributeProvider::provideSwitchRouteAttributeConfig($reflectionMethod)) {
            return $this->handleSwitchRoute($featureFlagConfig, $controller);
        }

        if (!$featureFlagConfig = FeatureFlagAttributeProvider::provideMethodStatusAttributeConfig($reflectionMethod)) {
            return null;
        }

        $this->logger->info('Check feature of Method: '.$methodName);
        $this->featureFlagStateChecker->check($featureFlagConfig);

        return null;
    }

    public function handleSwitchRoute(FeaturesFlagSwitchRoute $featureFlagConfig, object $controller): ?string
    {
        $isActive = $this->featureFlagProvider->areAllFeaturesActive($featureFlagConfig->features, $featureFlagConfig->context);
        $features = implode(',', $featureFlagConfig->features);

        if ($isActive) {
            $this->logger->warning("Feature '{$features}' is disabled for controller ".$controller::class);

            return $featureFlagConfig->switchedRoute;
        }

        $this->logger->info("Features '{$features}' are active for controller ".$controller::class);

        return null;
    }
}

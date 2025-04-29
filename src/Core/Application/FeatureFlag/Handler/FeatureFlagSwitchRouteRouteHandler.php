<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler;

use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagAttributeProvider;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;

class FeatureFlagSwitchRouteRouteHandler implements ControllerRouteHandlerInterface
{
    public function __construct(
        private readonly FeatureFlagProviderInterface $featureFlagProvider,
        private readonly ApplicationLoggerInterface $logger,
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public function handle(object $controller, string $methodName): ?string
    {
        $reflectionMethod = new \ReflectionMethod($methodName);

        if (!$featureFlagConfig = FeatureFlagAttributeProvider::provideSwitchRouteAttributeConfig($reflectionMethod)) {
            return null;
        }

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

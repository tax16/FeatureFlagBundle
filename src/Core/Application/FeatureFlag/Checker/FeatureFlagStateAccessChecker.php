<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Checker;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureInactive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Checker\FeatureFlagStateAccessCheckerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ApplicationLoggerInterface;

class FeatureFlagStateAccessChecker implements FeatureFlagStateAccessCheckerInterface
{
    public function __construct(
        private readonly FeatureFlagProviderInterface $featureFlagProvider,
        private readonly ApplicationLoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function check(IsFeatureInactive|IsFeatureActive $config): void
    {
        $features = $config->features;
        $exception = $config->exception;
        $isFeatureActivate = $this->featureFlagProvider->areAllFeaturesActive($features, $config->context);

        if (
            ($config instanceof IsFeatureActive && !$isFeatureActivate)
            || ($config instanceof IsFeatureInactive && $isFeatureActivate)
        ) {
            $this->logger->error('State of Feature(s) is not compliant');
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

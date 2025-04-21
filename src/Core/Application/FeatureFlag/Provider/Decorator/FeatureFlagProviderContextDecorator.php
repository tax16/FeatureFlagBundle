<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\Decorator;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Context\FeatureFlagContextInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Provider\FeatureFlagProviderInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\PortContainerInterface;

readonly class FeatureFlagProviderContextDecorator implements FeatureFlagProviderInterface
{
    public function __construct(
        private FeatureFlagProviderInterface $decoratedProvider,
        private PortContainerInterface $container,
    ) {
    }

    public function provideStateByFlag(string $flag, ?array $context = null): bool
    {
        if (!empty($context) && !$this->checkContext($context)) {
            return false;
        }

        return $this->decoratedProvider->provideStateByFlag($flag);
    }

    /**
     * @param array<FeatureFlagContextInterface> $context
     */
    private function checkContext(array $context): bool
    {
        foreach ($context as $ctx) {
            if (is_string($ctx) && class_exists($ctx)) {
                $ctx = $this->container->get($ctx);
            }

            if (!$ctx instanceof FeatureFlagContextInterface) {
                throw new \InvalidArgumentException('All context class should implement interface: FeatureFlagContextInterface');
            }

            if (!$ctx->isAllowed()) {
                return false;
            }
        }

        return true;
    }

    public function provideStateByFlags(array $flags, ?array $context = null): bool
    {
        if (!empty($context) && !$this->checkContext($context)) {
            return false;
        }

        return $this->decoratedProvider->provideStateByFlags($flags);
    }
}

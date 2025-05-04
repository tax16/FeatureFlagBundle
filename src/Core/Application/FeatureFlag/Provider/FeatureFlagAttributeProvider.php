<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchMethod;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchRoute;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureActive;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\IsFeatureInactive;

class FeatureFlagAttributeProvider
{
    private const SWITCH_CLASS_ATTRIBUTE = [
        FeatureFlagSwitchClass::class,
        FeaturesFlagSwitchClass::class,
    ];

    private const SWITCH_METHOD_ATTRIBUTE = [
        FeatureFlagSwitchMethod::class,
        FeaturesFlagSwitchMethod::class,
    ];

    private const STATUS_ATTRIBUTE = [
        IsFeatureActive::class,
        IsFeatureInactive::class,
    ];

    /**
     * @throws \ReflectionException
     */
    public static function provideClassAttributeConfig(mixed $service): FeatureFlagSwitchClass|FeaturesFlagSwitchClass|null
    {
        $reflection = new \ReflectionClass($service);

        /** @var FeatureFlagSwitchClass|FeaturesFlagSwitchClass|null $attribute */
        $attribute = self::resolveAttributeInstance(
            $reflection,
            self::SWITCH_CLASS_ATTRIBUTE
        );

        return $attribute;
    }

    public static function provideMethodAttributeConfig(\ReflectionMethod $method): FeatureFlagSwitchMethod|FeaturesFlagSwitchMethod|null
    {
        /** @var FeatureFlagSwitchMethod|FeaturesFlagSwitchMethod|null $attribute */
        $attribute = self::resolveAttributeInstance(
            $method,
            self::SWITCH_METHOD_ATTRIBUTE
        );

        return $attribute;
    }

    public static function provideMethodStatusAttributeConfig(\ReflectionMethod $method): IsFeatureInactive|IsFeatureActive|null
    {
        /** @var IsFeatureInactive|IsFeatureActive|null $attribute */
        $attribute = self::resolveAttributeInstance(
            $method,
            self::STATUS_ATTRIBUTE
        );

        return $attribute;
    }

    public static function provideClassStatusAttributeConfig(mixed $service): IsFeatureInactive|IsFeatureActive|null
    {
        $reflection = new \ReflectionClass($service);

        /** @var IsFeatureInactive|IsFeatureActive|null $attribute */
        $attribute = self::resolveAttributeInstance(
            $reflection,
            self::STATUS_ATTRIBUTE
        );

        return $attribute;
    }

    public static function provideSwitchRouteAttributeConfig(\ReflectionMethod $method): ?FeaturesFlagSwitchRoute
    {
        /** @var FeaturesFlagSwitchRoute|null $attribute */
        $attribute = self::resolveAttributeInstance(
            $method,
            [FeaturesFlagSwitchRoute::class]
        );

        return $attribute;
    }

    /**
     * @param array<class-string> $attributeClasses
     */
    private static function resolveAttributeInstance(
        mixed $reflection,
        array $attributeClasses,
    ): ?object {
        foreach ($attributeClasses as $attributeClass) {
            foreach ($reflection->getAttributes($attributeClass) as $attribute) {
                $instance = $attribute->newInstance();
                if ($instance instanceof $attributeClass) {
                    return $instance;
                }
            }
        }

        return null;
    }
}

<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider;

use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeatureFlagSwitchMethod;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchClass;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Attribute\FeaturesFlagSwitchMethod;

class ClassFeatureProvider
{
    private const SWITCH_CLASS_ATTRIBUTE = [
        FeatureFlagSwitchClass::class,
        FeaturesFlagSwitchClass::class,
    ];

    private const SWITCH_METHOD_ATTRIBUTE = [
        FeatureFlagSwitchMethod::class,
        FeaturesFlagSwitchMethod::class,
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

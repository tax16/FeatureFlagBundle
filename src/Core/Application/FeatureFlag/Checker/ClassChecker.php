<?php

namespace Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Checker;

class ClassChecker
{
    /**
     * @param string[] $filteredMethod
     *
     * @throws \ReflectionException
     */
    public static function areMethodsCompatible(
        mixed $originalClass,
        mixed $switchedClass,
        array $filteredMethod = [],
    ): bool {
        $originalReflection = new \ReflectionClass($originalClass);
        $switchedReflection = new \ReflectionClass($switchedClass);

        $originalMethods = $originalReflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($originalMethods as $originalMethod) {
            $methodName = $originalMethod->getName();
            if ('__construct' === $methodName) {
                continue;
            }

            if (!empty($filteredMethod) && !in_array($methodName, $filteredMethod, true)) {
                continue;
            }

            if (!$switchedReflection->hasMethod($originalMethod->getName())) {
                return false;
            }

            $switchedMethod = $switchedReflection->getMethod($originalMethod->getName());

            if (!self::areParametersCompatible($originalMethod, $switchedMethod)) {
                return false;
            }
        }

        return true;
    }

    public static function areParametersCompatible(\ReflectionMethod $originalMethod, \ReflectionMethod $switchedMethod): bool
    {
        $originalParams = $originalMethod->getParameters();
        $switchedParams = $switchedMethod->getParameters();

        if (count($originalParams) !== count($switchedParams)) {
            return false;
        }

        foreach ($originalParams as $index => $originalParam) {
            $switchedParam = $switchedParams[$index];

            $originalType = $originalParam->getType();
            $switchedType = $switchedParam->getType();

            if (
                $originalType instanceof \ReflectionNamedType
                && $switchedType instanceof \ReflectionNamedType
                && $originalType->getName() !== $switchedType->getName()
            ) {
                return false;
            }

            if ($originalParam->isOptional() !== $switchedParam->isOptional()) {
                return false;
            }
        }

        return true;
    }
}

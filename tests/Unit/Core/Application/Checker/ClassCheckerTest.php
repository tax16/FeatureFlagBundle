<?php

namespace App\Tests\Unit\Core\Application\Checker;

use App\Tests\Unit\Core\Application\Checker\FakeClass\CompatibleOriginal;
use App\Tests\Unit\Core\Application\Checker\FakeClass\CompatibleSwitched;
use App\Tests\Unit\Core\Application\Checker\FakeClass\MissingMethodSwitched;
use App\Tests\Unit\Core\Application\Checker\FakeClass\OptionalMismatchSwitched;
use App\Tests\Unit\Core\Application\Checker\FakeClass\PartialSwitched;
use App\Tests\Unit\Core\Application\Checker\FakeClass\TypeMismatchSwitched;
use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Checker\ClassChecker;

class ClassCheckerTest extends TestCase
{
    public function test_methods_are_compatible(): void
    {
        $result = ClassChecker::areMethodsCompatible(
            CompatibleOriginal::class,
            CompatibleSwitched::class
        );

        $this->assertTrue($result);
    }

    public function test_methods_are_not_compatible_due_to_missing_method(): void
    {
        $result = ClassChecker::areMethodsCompatible(
            CompatibleOriginal::class,
            MissingMethodSwitched::class
        );

        $this->assertFalse($result);
    }

    public function test_methods_are_not_compatible_due_to_type_mismatch(): void
    {
        $result = ClassChecker::areMethodsCompatible(
            CompatibleOriginal::class,
            TypeMismatchSwitched::class
        );

        $this->assertFalse($result);
    }

    public function test_methods_are_compatible_when_filtered(): void
    {
        $result = ClassChecker::areMethodsCompatible(
            CompatibleOriginal::class,
            PartialSwitched::class,
            ['onlyThisOne']
        );

        $this->assertTrue($result);
    }

    public function test_parameters_compatible_check(): void
    {
        $original = new \ReflectionMethod(CompatibleOriginal::class, 'doSomething');
        $switched = new \ReflectionMethod(CompatibleSwitched::class, 'doSomething');

        $this->assertTrue(ClassChecker::areParametersCompatible($original, $switched));
    }

    public function test_parameters_not_compatible_due_to_optional_diff(): void
    {
        $original = new \ReflectionMethod(CompatibleOriginal::class, 'optionalMethod');
        $switched = new \ReflectionMethod(OptionalMismatchSwitched::class, 'optionalMethod');

        $this->assertFalse(ClassChecker::areParametersCompatible($original, $switched));
    }
}
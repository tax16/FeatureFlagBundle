<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass\FakeClass;

class FakeServiceSwitchedIncompatibleParams
{
    public function execute(int $number): string
    {
        return "Method with incompatible parameters";
    }
}
<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass\FakeClass;

class FakeServiceSwitched
{
    public function execute(): string
    {
        return "Switched Method";
    }
}
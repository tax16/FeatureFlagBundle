<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\Proxy\FakeClass;

class FakeClassOne
{
    public function execute(): string
    {
        return "Original Method";
    }
}
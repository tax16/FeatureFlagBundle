<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;

class FakeClassOne
{
    public function execute(): string
    {
        return "Original Method";
    }
}
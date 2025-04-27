<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;

class FakeSwitchedServiceWithIncompatibleMethods
{
    public function someMethod(int $test): string
    {
        return 'service method';
    }
}
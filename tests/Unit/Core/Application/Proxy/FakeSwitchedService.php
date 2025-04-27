<?php

namespace App\Tests\Unit\Core\Application\Proxy;

class FakeSwitchedService
{
    public function someMethod(): string
    {
        return 'switched service method';
    }
}
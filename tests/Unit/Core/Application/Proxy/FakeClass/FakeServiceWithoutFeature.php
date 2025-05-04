<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;

class FakeServiceWithoutFeature
{
    public function someMethod(): string
    {
        return 'ok';
    }
}
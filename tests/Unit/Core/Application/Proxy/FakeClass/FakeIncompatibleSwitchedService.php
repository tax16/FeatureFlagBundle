<?php

namespace App\Tests\Unit\Core\Application\Proxy\FakeClass;

class FakeIncompatibleSwitchedService
{
    public function incompatibleMethod(): string
    {
        return 'incompatible';
    }
}
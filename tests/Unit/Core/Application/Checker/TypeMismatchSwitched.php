<?php
namespace App\Tests\Unit\Core\Application\Checker;

class TypeMismatchSwitched
{
    public function doSomething(int $name): string { return ''; } // <-- type diff
    public function optionalMethod(int $a = 5): int { return $a; }
    public function onlyThisOne(string $id): void {}
}

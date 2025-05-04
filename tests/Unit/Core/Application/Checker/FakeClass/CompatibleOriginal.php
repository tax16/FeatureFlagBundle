<?php
namespace App\Tests\Unit\Core\Application\Checker\FakeClass;

class CompatibleOriginal
{
    public function doSomething(string $name): string { return ''; }
    public function optionalMethod(int $a = 5): int { return $a; }
    public function onlyThisOne(string $id): void {}
}
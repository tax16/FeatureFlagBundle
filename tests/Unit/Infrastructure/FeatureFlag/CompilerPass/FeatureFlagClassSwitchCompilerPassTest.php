<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass;

use App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass\FakeClass\FakeService;
use App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass\FakeClass\FakeServiceSwitched;
use App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass\FakeClass\FakeServiceSwitchNoClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Provider\FeatureFlagProvider;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\FeatureFlagClassSwitchCompilerPass;

class FeatureFlagClassSwitchCompilerPassTest extends TestCase
{
    protected function tearDown(): void
    {
        FeatureFlagProvider::resetCache();
    }
    
    public function testCompilerPassSwitchesServiceSwitch(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition(FakeService::class);
        $container->setDefinition(FakeService::class, $definition);

        $compilerPass = new FeatureFlagClassSwitchCompilerPass();
        $compilerPass->process($container);

        $this->assertSame(FakeService::class, $container->getDefinition(FakeService::class)->getClass());
    }

    public function testCompilerPassSwitchesServiceWhenNoAttribute(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition(FakeServiceSwitched::class);
        $container->setDefinition(FakeServiceSwitched::class, $definition);

        $compilerPass = new FeatureFlagClassSwitchCompilerPass();
        $compilerPass->process($container);

        $this->assertSame(FakeServiceSwitched::class, $container->getDefinition(FakeServiceSwitched::class)->getClass());
    }

    public function testCompilerPassThrowsExceptionWhenClassNotExist(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("The switched class 'fake_class' does not exist");

        $container = new ContainerBuilder();

        $definition = new Definition(FakeServiceSwitchNoClass::class);
        $container->setDefinition(FakeServiceSwitchNoClass::class, $definition);

        $compilerPass = new FeatureFlagClassSwitchCompilerPass();
        $compilerPass->process($container);
    }
}
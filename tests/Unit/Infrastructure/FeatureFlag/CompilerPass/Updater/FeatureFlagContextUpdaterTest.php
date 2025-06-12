<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\CompilerPass\Updater;

use Codeception\PHPUnit\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\CompilerPass\Updater\FeatureFlagContextUpdater;

class FeatureFlagContextUpdaterTest extends TestCase
{
    public function testUpdateContextClassToPublic(): void
    {
        $updater = new class extends FeatureFlagContextUpdater {
            public function publicUpdateContextClassToPublic(array $context, ContainerBuilder $container): void
            {
                $this->updateContextClassToPublic($context, $container);
            }
        };

        $container = $this->createMock(ContainerBuilder::class);

        $serviceId1 = 'service.non_public.definition';
        $serviceId2 = 'service.public.alias';
        $serviceId3 = 'service.unknown';

        $definition = $this->createMock(Definition::class);
        $definition->expects($this->once())
            ->method('isPublic')
            ->willReturn(false);
        $definition->expects($this->once())
            ->method('setPublic')
            ->with(true);

        $alias = $this->createMock(Alias::class);
        $alias->expects($this->once())
            ->method('isPublic')
            ->willReturn(false);
        $alias->expects($this->once())
            ->method('setPublic')
            ->with(true);

        $container->method('hasDefinition')
            ->willReturnCallback(fn($id) => $id === $serviceId1);
        $container->method('getDefinition')
            ->with($serviceId1)
            ->willReturn($definition);

        $container->method('hasAlias')
            ->willReturnCallback(fn($id) => $id === $serviceId2);
        $container->method('getAlias')
            ->with($serviceId2)
            ->willReturn($alias);

        $updater->publicUpdateContextClassToPublic(
            [$serviceId1, $serviceId2, $serviceId3],
            $container
        );
    }
}
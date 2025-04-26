<?php

namespace App\Tests\Unit\Core\Application\Handler;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler\FeatureFlagDeleteCommandHandler;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository\FeatureFlagRepositoryInterface;

class DeleteFeatureFlagHandlerTest extends TestCase
{
    private $repositoryMock;
    private $handler;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(FeatureFlagRepositoryInterface::class);

        $this->handler = new FeatureFlagDeleteCommandHandler($this->repositoryMock);
    }

    public function testHandleDeletesFeatureFlagWhenExists()
    {
        $flagName = 'test_flag';

        $featureFlagMock = $this->createMock(FeatureFlag::class);

        $this->repositoryMock->method('findByName')->with($flagName)->willReturn($featureFlagMock);

        $this->repositoryMock->expects($this->once())->method('delete')->with($featureFlagMock);

        $this->handler->handle($flagName);
    }

    public function testHandleThrowsExceptionWhenFeatureFlagNotFound()
    {
        $flagName = 'unknown_flag';

        $this->repositoryMock->method('findByName')->with($flagName)->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Feature flag '$flagName' not found.");

        // Exécution du handler
        $this->handler->handle($flagName);
    }
}
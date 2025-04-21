<?php

namespace App\Tests\Unit\Core\Application\Handler;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Handler\SaveFeatureFlagHandler;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository\FeatureFlagRepositoryInterface;

class SaveFeatureFlagHandlerTest extends TestCase
{
    private $repositoryMock;
    private $handler;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(FeatureFlagRepositoryInterface::class);

        $this->handler = new SaveFeatureFlagHandler($this->repositoryMock);
    }

    public function testHandleCreatesNewFeatureFlagIfNotExists()
    {
        $flagName = 'new_flag';
        $enabled = true;
        $startDate = new DateTime('2024-01-01');
        $endDate = new DateTime('2024-12-31');

        $this->repositoryMock->method('findByName')->with($flagName)->willReturn(null);

        $this->repositoryMock->expects($this->once())->method('save');

        $this->handler->handle($flagName, $enabled, $startDate, $endDate);
    }

    public function testHandleUpdatesExistingFeatureFlag()
    {
        $flagName = 'existing_flag';
        $enabled = false;
        $startDate = new DateTime('2024-06-01');
        $endDate = new DateTime('2024-12-31');

        $featureFlagMock = $this->createMock(FeatureFlag::class);
        $featureFlagMock->method('getName')->willReturn($flagName);

        $featureFlagMock->expects($this->once())->method('update')->with($enabled, $startDate, $endDate);

        $this->repositoryMock->method('findByName')->with($flagName)->willReturn($featureFlagMock);

        $this->repositoryMock->expects($this->once())->method('save')->with($featureFlagMock);

        $this->handler->handle($flagName, $enabled, $startDate, $endDate);
    }
}
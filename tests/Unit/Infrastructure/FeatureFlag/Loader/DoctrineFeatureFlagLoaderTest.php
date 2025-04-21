<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\Loader;

use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\DoctrineFeatureFlagLoader;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Persistence\DoctrineFeatureFlagRepository;

class DoctrineFeatureFlagLoaderTest extends TestCase
{
    private $repositoryMock;
    private $loader;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(DoctrineFeatureFlagRepository::class);

        $this->loader = new DoctrineFeatureFlagLoader($this->repositoryMock);
    }

    public function testLoadFeatureFlagsReturnsFeatureFlags(): void
    {
        $featureFlags = [
            new FeatureFlag('feature_1', true),
            new FeatureFlag('feature_2', false),
        ];

        $this->repositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn($featureFlags);

        $result = $this->loader->loadFeatureFlags();

        $this->assertSame($featureFlags, $result);
    }

    public function testSupportsReturnsTrueForDoctrineStorage(): void
    {
        $this->assertTrue($this->loader->supports(FeatureFlagStorageType::DOCTRINE->value));
    }

    public function testSupportsReturnsFalseForNonDoctrineStorage(): void
    {
        $this->assertFalse($this->loader->supports('redis'));
        $this->assertFalse($this->loader->supports('file'));
    }
}
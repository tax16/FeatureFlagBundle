<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\Loader;


use PHPUnit\Framework\TestCase;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\JsonFeatureFlagLoader;

class JsonFeatureFlagLoaderTest extends TestCase
{
    private string $jsonFilePath;

    protected function setUp(): void
    {
        $this->jsonFilePath = tempnam(sys_get_temp_dir(), 'json_feature_flags');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->jsonFilePath)) {
            unlink($this->jsonFilePath);
        }
    }

    public function testLoadFeatureFlags(): void
    {
        $jsonData = json_encode([
            [
                'name' => 'feature_1',
                'enabled' => true,
                'start_date' => '2022-03-01T00:00:00+00:00',
                'end_date' => '2999-12-31T23:59:59+00:00'
            ],
            [
                'name' => 'feature_2',
                'enabled' => false,
                'start_date' => null,
                'end_date' => null
            ]
        ], JSON_THROW_ON_ERROR);

        file_put_contents($this->jsonFilePath, $jsonData);

        $loader = new JsonFeatureFlagLoader($this->jsonFilePath);

        $featureFlags = $loader->loadFeatureFlags();

        $this->assertCount(2, $featureFlags);

        /** @var FeatureFlag $flag1 */
        $flag1 = $featureFlags[0];
        $this->assertEquals('feature_1', $flag1->getName());
        $this->assertTrue($flag1->isEnabled());
        $this->assertEquals(new \DateTime('2022-03-01T00:00:00+00:00'), $flag1->getStartDate());
        $this->assertEquals(new \DateTime('2999-12-31T23:59:59+00:00'), $flag1->getEndDate());

        /** @var FeatureFlag $flag2 */
        $flag2 = $featureFlags[1];
        $this->assertEquals('feature_2', $flag2->getName());
        $this->assertFalse($flag2->isEnabled());
        $this->assertNull($flag2->getStartDate());
        $this->assertNull($flag2->getEndDate());
    }

    public function testSupportsReturnsTrueForJsonStorage(): void
    {
        $loader = new JsonFeatureFlagLoader($this->jsonFilePath);
        $this->assertTrue($loader->supports(FeatureFlagStorageType::JSON->value));
    }

    public function testSupportsReturnsFalseForNonJsonStorage(): void
    {
        $loader = new JsonFeatureFlagLoader($this->jsonFilePath);
        $this->assertFalse($loader->supports('doctrine'));
        $this->assertFalse($loader->supports('redis'));
    }

    public function testLoadFeatureFlagsThrowsExceptionOnInvalidJson(): void
    {
        file_put_contents($this->jsonFilePath, 'invalid json');

        $loader = new JsonFeatureFlagLoader($this->jsonFilePath);

        $this->expectException(\JsonException::class);
        $loader->loadFeatureFlags();
    }
}
<?php

namespace App\Tests\Unit\Infrastructure\FeatureFlag\Loader;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\YamlFeatureFlagLoader;
use TypeError;

class YamlFeatureFlagLoaderTest extends TestCase
{
    private string $yamlFilePath;

    protected function setUp(): void
    {
        $this->yamlFilePath = tempnam(sys_get_temp_dir(), 'yaml_feature_flags');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->yamlFilePath)) {
            unlink($this->yamlFilePath);
        }
    }

    public function testLoadFeatureFlags(): void
    {
        $yamlData = Yaml::dump([
            [
                'name' => 'feature_1',
                'enabled' => true,
                'start_date' => '2024-03-01T00:00:00+00:00',
                'end_date' => '2999-12-31T23:59:59+00:00'
            ],
            [
                'name' => 'feature_2',
                'enabled' => false,
                'start_date' => null,
                'end_date' => null
            ]
        ]);

        file_put_contents($this->yamlFilePath, $yamlData);

        $loader = new YamlFeatureFlagLoader($this->yamlFilePath);

        $featureFlags = $loader->loadFeatureFlags();

        $this->assertCount(2, $featureFlags);

        /** @var FeatureFlag $flag1 */
        $flag1 = $featureFlags[0];
        $this->assertEquals('feature_1', $flag1->getName());
        $this->assertTrue($flag1->isEnabled());
        $this->assertEquals(new \DateTime('2024-03-01T00:00:00+00:00'), $flag1->getStartDate());
        $this->assertEquals(new \DateTime('2999-12-31T23:59:59+00:00'), $flag1->getEndDate());

        /** @var FeatureFlag $flag2 */
        $flag2 = $featureFlags[1];
        $this->assertEquals('feature_2', $flag2->getName());
        $this->assertFalse($flag2->isEnabled());
        $this->assertNull($flag2->getStartDate());
        $this->assertNull($flag2->getEndDate());
    }

    public function testSupportsReturnsTrueForYamlStorage(): void
    {
        $loader = new YamlFeatureFlagLoader($this->yamlFilePath);
        $this->assertTrue($loader->supports(FeatureFlagStorageType::YAML->value));
    }

    public function testSupportsReturnsFalseForNonYamlStorage(): void
    {
        $loader = new YamlFeatureFlagLoader($this->yamlFilePath);
        $this->assertFalse($loader->supports('doctrine'));
        $this->assertFalse($loader->supports('json'));
    }

    public function testLoadFeatureFlagsThrowsExceptionOnInvalidYaml(): void
    {
        file_put_contents($this->yamlFilePath, 'invalid yaml');

        $loader = new YamlFeatureFlagLoader($this->yamlFilePath);

        $this->expectException(TypeError::class);
        $loader->loadFeatureFlags();
    }
}
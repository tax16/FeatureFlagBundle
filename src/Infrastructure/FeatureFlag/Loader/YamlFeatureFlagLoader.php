<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactoryInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\Trait\FeatureFlagLoaderTrait;

#[Autoconfigure(tags: ['feature_flag_loader'])]
class YamlFeatureFlagLoader implements FeatureFlagLoaderFactoryInterface
{
    use FeatureFlagLoaderTrait;
    private ?string $yamlPath = null;

    public function __construct(
        #[Autowire(param: 'feature_flags.storage.path')]
        ?string $yamlPath = null,
    ) {
        $this->yamlPath = $yamlPath;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function loadFeatureFlags(): array
    {
        if (!$this->yamlPath) {
            throw new \InvalidArgumentException('Invalid yaml path');
        }
        $data = Yaml::parseFile($this->yamlPath);

        return $this->parseFeatureFlags($data);
    }

    public function supports(string $storageType): bool
    {
        return $this->yamlPath && $storageType === FeatureFlagStorageType::YAML->value;
    }
}

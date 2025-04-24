<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tax16\FeatureFlagBundle\Core\Application\FeatureFlag\Factory\FeatureFlagLoaderFactoryInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Enum\FeatureFlagStorageType;
use Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Loader\Trait\FeatureFlagLoaderTrait;

#[Autoconfigure(tags: ['feature_flag_loader'])]
class JsonFeatureFlagLoader implements FeatureFlagLoaderFactoryInterface
{
    use FeatureFlagLoaderTrait;

    private ?string $jsonPath = null;

    public function __construct(
        #[Autowire(param: 'feature_flags.storage.path')]
        ?string $jsonPath = null,
    ) {
        $this->jsonPath = $jsonPath;
    }

    /**
     * @throws \JsonException|\DateMalformedStringException
     */
    public function loadFeatureFlags(): array
    {
        if (!$this->jsonPath || !$path = file_get_contents($this->jsonPath)) {
            throw new \InvalidArgumentException('Invalid json path: '.$this->jsonPath);
        }

        $data = json_decode($path, true, 512, JSON_THROW_ON_ERROR);

        return $this->parseFeatureFlags($data);
    }

    public function supports(string $storageType): bool
    {
        return $this->jsonPath && $storageType === FeatureFlagStorageType::JSON->value;
    }
}

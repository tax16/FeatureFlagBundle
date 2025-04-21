<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\Adapter;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tax16\FeatureFlagBundle\Core\Domain\Port\ConfigurationProviderInterface;

class ParameterBagAdapter implements ConfigurationProviderInterface
{
    private ParameterBagInterface $parameters;

    public function __construct(ParameterBagInterface $parameterSource)
    {
        $this->parameters = $parameterSource;
    }

    public function get(string $key)
    {
        return $this->parameters->get($key);
    }
}

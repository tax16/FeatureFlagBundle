<?php

namespace Tax16\FeatureFlagBundle\Infrastructure\FeatureFlag\Persistence;

use Doctrine\ORM\EntityManagerInterface;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity\FeatureFlag;
use Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Repository\FeatureFlagRepositoryInterface;

class DoctrineFeatureFlagRepository implements FeatureFlagRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(FeatureFlag $featureFlag): void
    {
        $this->entityManager->persist($featureFlag);
        $this->entityManager->flush();
    }

    public function findByName(string $name): ?FeatureFlag
    {
        return $this->entityManager
            ->getRepository(FeatureFlag::class)
            ->findOneBy(['name' => $name]);
    }

    public function findByNames(array $names): ?array
    {
        return $this->entityManager
            ->getRepository(FeatureFlag::class)
            ->findBy(['name' => $names]);
    }

    public function delete(FeatureFlag $featureFlag): void
    {
        $this->entityManager->remove($featureFlag);
        $this->entityManager->flush();
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(FeatureFlag::class)
            ->findAll();
    }
}

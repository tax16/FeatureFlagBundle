<?php

namespace Tax16\FeatureFlagBundle\Core\Domain\FeatureFlag\Entity;

class FeatureFlag
{
    private string $name;
    private bool $enabled;
    private ?\DateTimeInterface $startDate;
    private ?\DateTimeInterface $endDate;

    public function __construct(
        string $name,
        bool $enabled,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ) {
        $this->name = $name;
        $this->enabled = $enabled;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function update(
        ?bool $enabled = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): void {
        if (null !== $enabled) {
            $this->enabled = $enabled;
        }

        if (null !== $startDate) {
            $this->startDate = $startDate;
        }

        if (null !== $endDate) {
            $this->endDate = $endDate;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $now = new \DateTime();

        if ($this->endDate && $now > $this->endDate) {
            return false;
        }

        if ($this->startDate && $now < $this->startDate) {
            return false;
        }

        return true;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'enabled' => $this->enabled,
            'startDate' => $this->startDate?->format(\DateTimeInterface::ATOM),
            'endDate' => $this->endDate?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \DateMalformedStringException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['enabled'],
            isset($data['startDate']) ? new \DateTimeImmutable($data['startDate']) : null,
            isset($data['endDate']) ? new \DateTimeImmutable($data['endDate']) : null,
        );
    }
}

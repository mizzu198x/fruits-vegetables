<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PlantType;
use App\Repository\PlantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PlantRepository::class)]
#[ORM\Table(name: 'plant')]
#[UniqueEntity('golden_id')]
class Plant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::INTEGER)]
    private int $goldenId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(enumType: PlantType::class)]
    private PlantType $type;

    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getGoldenId(): int
    {
        return $this->goldenId;
    }

    public function setGoldenId(int $goldenId): self
    {
        $this->goldenId = $goldenId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): PlantType
    {
        return $this->type;
    }

    public function setType(PlantType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Contract\Request\Body\Trait;

use App\Enum\UnitType;
use Symfony\Component\Validator\Constraints as Assert;

trait PlantRequestTrait
{
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    public string $name;

    #[Assert\Type(type: 'float')]
    #[Assert\NotBlank]
    public float $quantity;

    #[Assert\Choice(choices: [UnitType::GRAM, UnitType::KILOGRAM])]
    #[Assert\NotNull]
    public UnitType $unit;
}

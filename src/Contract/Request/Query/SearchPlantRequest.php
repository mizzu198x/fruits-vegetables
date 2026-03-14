<?php

declare(strict_types=1);

namespace App\Contract\Request\Query;

use App\Contract\Request\Interface\RequestQueryInterface;
use App\Contract\Request\Interface\ValidatableRequestInterface;
use App\Enum\UnitType;
use Symfony\Component\Validator\Constraints as Assert;

class SearchPlantRequest implements RequestQueryInterface, ValidatableRequestInterface
{
    #[Assert\Type(type: 'string')]
    public ?string $query = null;

    #[Assert\Type(type: 'float')]
    public ?float $minQty = null;

    #[Assert\Type(type: 'float')]
    public ?float $maxQty = null;

    #[Assert\Choice(choices: [UnitType::GRAM, UnitType::KILOGRAM])]
    public UnitType $unit = UnitType::GRAM;
}

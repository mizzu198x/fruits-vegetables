<?php

declare(strict_types=1);

namespace App\Helper;

use App\Enum\UnitType;

class UnitConverter
{
    public function toGrams(float|int $quantity, UnitType $unit): int
    {
        return match ($unit) {
            UnitType::GRAM => (int) \ceil($quantity),
            UnitType::KILOGRAM => (int) \ceil($quantity * 1000),
        };
    }

    public function fromGrams(int $grams, UnitType $unit): float|int
    {
        return match ($unit) {
            UnitType::GRAM => $grams,
            UnitType::KILOGRAM => $grams / 1000,
        };
    }
}

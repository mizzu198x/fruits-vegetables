<?php

declare(strict_types=1);

namespace App\Service\Collection;

use App\Enum\PlantType;

class FruitCollection extends AbstractPlantCollection
{
    public function getType(): PlantType
    {
        return PlantType::FRUIT;
    }
}

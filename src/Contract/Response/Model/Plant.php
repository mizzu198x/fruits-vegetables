<?php

declare(strict_types=1);

namespace App\Contract\Response\Model;

use App\Enum\UnitType;
use JMS\Serializer\Annotation as JMS;

class Plant
{
    #[JMS\Type(name: 'int')]
    public int $id;

    #[JMS\Type(name: 'int')]
    public int $goldenId;

    #[JMS\Type(name: 'string')]
    public string $name;

    #[JMS\Type(name: 'float')]
    public float $quantity;

    public UnitType $unit;
}

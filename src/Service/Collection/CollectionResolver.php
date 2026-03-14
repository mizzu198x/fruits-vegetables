<?php

declare(strict_types=1);

namespace App\Service\Collection;

use App\Enum\PlantType;
use App\Exception\Http\UnprocessableEntityException;

class CollectionResolver
{
    public function __construct(private readonly array $collections)
    {
    }

    public function resolve(PlantType $type): CollectionInterface
    {
        foreach ($this->collections as $collection) {
            if ($collection->getType() === $type) {
                return $collection;
            }
        }

        throw new UnprocessableEntityException();
    }
}

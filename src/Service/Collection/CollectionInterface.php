<?php

declare(strict_types=1);

namespace App\Service\Collection;

use App\Contract\Request\Query\SearchPlantRequest;
use App\Entity\Plant;

interface CollectionInterface
{
    public function add(Plant $plant): void;

    public function remove(int $id): void;

    public function list(): array;

    public function search(SearchPlantRequest $searchCriteria): array;
}

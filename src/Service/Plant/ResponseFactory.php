<?php

declare(strict_types=1);

namespace App\Service\Plant;

use App\Contract\Response\SearchPlantResponse;
use App\Enum\UnitType;

class ResponseFactory
{
    public function __construct(private readonly DataProcessor $dataProcessor)
    {
    }

    public function createSearchResponse(array $items, UnitType $unit): SearchPlantResponse
    {
        $searchPlantResponse = new SearchPlantResponse();
        foreach ($items as $item) {
            $searchPlantResponse->items[] = $this->dataProcessor->convertEntityToResponseModel($item, $unit);
        }

        return $searchPlantResponse;
    }
}

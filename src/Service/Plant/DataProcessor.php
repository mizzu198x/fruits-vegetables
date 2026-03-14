<?php

declare(strict_types=1);

namespace App\Service\Plant;

use App\Contract\Request\Body\BroadcastPlantRequest;
use App\Contract\Request\Body\CreatePlantRequest;
use App\Contract\Request\Body\UpdatePlantRequest;
use App\Contract\Response\Model\Plant as ResponseModel;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Enum\UnitType;
use App\Helper\UnitConverter;

class DataProcessor
{
    public function __construct(private readonly UnitConverter $unitConverter)
    {
    }

    public function copyFromRequestToEntity(Plant $plant, BroadcastPlantRequest|UpdatePlantRequest $request): void
    {
        if ($request instanceof BroadcastPlantRequest) {
            $plant->setGoldenId($request->id);
            $plant->setType($request->type);
        } else {
            $plant->setGoldenId($request->goldenId);
        }
        $plant->setName($request->name);
        $plant->setQuantity($this->unitConverter->toGrams($request->quantity, $request->unit));
    }

    public function convertRequestToEntity(CreatePlantRequest $request, PlantType $type): Plant
    {
        $plant = new Plant();
        $plant->setGoldenId($request->goldenId);
        $plant->setName($request->name);
        $plant->setType($type);
        $plant->setQuantity($this->unitConverter->toGrams($request->quantity, $request->unit));

        return $plant;
    }

    public function convertEntityToResponseModel(Plant $plant, UnitType $unit): ResponseModel
    {
        $responseModel = new ResponseModel();
        $responseModel->id = $plant->getId();
        $responseModel->goldenId = $plant->getGoldenId();
        $responseModel->name = $plant->getName();
        $responseModel->quantity = $this->unitConverter->fromGrams($plant->getQuantity(), $unit);
        $responseModel->unit = $unit;

        return $responseModel;
    }
}

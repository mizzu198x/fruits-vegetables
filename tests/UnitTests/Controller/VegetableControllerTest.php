<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Controller;

use App\Contract\Request\Body\CreatePlantRequest;
use App\Contract\Request\Body\UpdatePlantRequest;
use App\Contract\Request\Query\SearchPlantRequest;
use App\Contract\Response\SearchPlantResponse;
use App\Controller\VegetableController;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Enum\UnitType;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Repository\PlantRepository;
use App\Service\Collection\VegetableCollection;
use App\Service\Plant\DataProcessor;
use App\Service\Plant\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class VegetableControllerTest extends TestCase
{
    private PlantRepository|MockObject $plantRepository;
    private VegetableCollection|MockObject $collection;
    private DataProcessor|MockObject $dataProcessor;
    private ResponseFactory|MockObject $responseFactory;
    private VegetableController $controller;

    protected function setUp(): void
    {
        $this->plantRepository = $this->createMock(PlantRepository::class);
        $this->collection = $this->createMock(VegetableCollection::class);
        $this->dataProcessor = $this->createMock(DataProcessor::class);
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->controller = new VegetableController(
            $this->plantRepository,
            $this->collection,
            $this->dataProcessor,
            $this->responseFactory,
        );
    }

    public function testSearch(): void
    {
        $request = new SearchPlantRequest();
        $request->query = 'car';
        $request->minQty = 10.0;
        $request->maxQty = 100.0;
        $request->unit = UnitType::GRAM;
        $items = [$this->createPlant(2, 200, 'Carrot', PlantType::VEGETABLE, 150)];
        $response = new SearchPlantResponse();

        $this->collection->expects($this->once())->method('search')->with($request)->willReturn($items);
        $this->responseFactory
            ->expects($this->once())
            ->method('createSearchResponse')
            ->with($items, UnitType::GRAM)
            ->willReturn($response);

        $this->assertSame($response, $this->controller->search($request));
    }

    public function testAddThrowsAlreadyExists(): void
    {
        $request = $this->createCreatePlantRequest();
        $existingPlant = $this->createPlant(2, $request->goldenId, 'Carrot', PlantType::VEGETABLE, 100);

        $this->plantRepository
            ->expects($this->once())
            ->method('findByGoldenId')
            ->with($request->goldenId)
            ->willReturn($existingPlant);
        $this->collection->expects($this->never())->method('add');

        $this->expectException(ResourceConflictException::class);
        $this->expectExceptionMessage('Vegetable already existed');

        $this->controller->add($request);
    }

    public function testAddPersistsNewVegetable(): void
    {
        $request = $this->createCreatePlantRequest();
        $newPlant = $this->createPlant(0, $request->goldenId, $request->name, PlantType::VEGETABLE, 450);

        $this->plantRepository
            ->expects($this->once())
            ->method('findByGoldenId')
            ->with($request->goldenId)
            ->willReturn(null);
        $this->dataProcessor
            ->expects($this->once())
            ->method('convertRequestToEntity')
            ->with($request, PlantType::VEGETABLE)
            ->willReturn($newPlant);
        $this->collection->expects($this->once())->method('add')->with($newPlant);

        $response = $this->controller->add($request);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testUpdateThrowsNotFound(): void
    {
        $request = new UpdatePlantRequest();
        $request->goldenId = 120;
        $request->name = 'Spinach';
        $request->quantity = 0.5;
        $request->unit = UnitType::KILOGRAM;

        $this->plantRepository->expects($this->once())->method('find')->with(8)->willReturn(null);
        $this->dataProcessor->expects($this->never())->method('copyFromRequestToEntity');

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Vegetable not found');

        $this->controller->update(8, $request);
    }

    public function testUpdatePersistsVegetable(): void
    {
        $request = new UpdatePlantRequest();
        $request->goldenId = 120;
        $request->name = 'Spinach';
        $request->quantity = 0.5;
        $request->unit = UnitType::KILOGRAM;
        $plant = $this->createPlant(8, 77, 'Old Spinach', PlantType::VEGETABLE, 250);

        $this->plantRepository->expects($this->once())->method('find')->with(8)->willReturn($plant);
        $this->dataProcessor->expects($this->once())->method('copyFromRequestToEntity')->with($plant, $request);
        $this->collection->expects($this->once())->method('add')->with($plant);

        $response = $this->controller->update(8, $request);

        $this->assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $this->collection->expects($this->once())->method('remove')->with(16);

        $response = $this->controller->delete(16);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    private function createCreatePlantRequest(): CreatePlantRequest
    {
        $request = new CreatePlantRequest();
        $request->goldenId = 321;
        $request->name = 'Carrot';
        $request->quantity = 450.0;
        $request->unit = UnitType::GRAM;

        return $request;
    }

    private function createPlant(int $id, int $goldenId, string $name, PlantType $type, int $quantity): Plant
    {
        return (new Plant())
            ->setId($id)
            ->setGoldenId($goldenId)
            ->setName($name)
            ->setType($type)
            ->setQuantity($quantity);
    }
}

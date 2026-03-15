<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Controller;

use App\Contract\Request\Body\CreatePlantRequest;
use App\Contract\Request\Body\UpdatePlantRequest;
use App\Contract\Request\Query\SearchPlantRequest;
use App\Contract\Response\SearchPlantResponse;
use App\Controller\FruitController;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Enum\UnitType;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Repository\PlantRepository;
use App\Service\Collection\FruitCollection;
use App\Service\Plant\DataProcessor;
use App\Service\Plant\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class FruitControllerTest extends TestCase
{
    private PlantRepository|MockObject $plantRepository;
    private FruitCollection|MockObject $collection;
    private DataProcessor|MockObject $dataProcessor;
    private ResponseFactory|MockObject $responseFactory;
    private FruitController $controller;

    protected function setUp(): void
    {
        $this->plantRepository = $this->createMock(PlantRepository::class);
        $this->collection = $this->createMock(FruitCollection::class);
        $this->dataProcessor = $this->createMock(DataProcessor::class);
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->controller = new FruitController(
            $this->plantRepository,
            $this->collection,
            $this->dataProcessor,
            $this->responseFactory,
        );
    }

    public function testSearch(): void
    {
        $request = new SearchPlantRequest();
        $request->query = 'app';
        $request->minQty = 1.0;
        $request->maxQty = 2.0;
        $request->unit = UnitType::KILOGRAM;
        $items = [$this->createPlant(1, 100, 'Apple', PlantType::FRUIT, 1000)];
        $response = new SearchPlantResponse();

        $this->collection->expects($this->once())->method('search')->with($request)->willReturn($items);
        $this->responseFactory
            ->expects($this->once())
            ->method('createSearchResponse')
            ->with($items, UnitType::KILOGRAM)
            ->willReturn($response);

        $this->assertSame($response, $this->controller->search($request));
    }

    public function testAddThrowsAlreadyExists(): void
    {
        $request = $this->createCreatePlantRequest();
        $existingPlant = $this->createPlant(1, $request->goldenId, 'Apple', PlantType::FRUIT, 1000);

        $this->plantRepository
            ->expects($this->once())
            ->method('findByGoldenId')
            ->with($request->goldenId)
            ->willReturn($existingPlant);
        $this->collection->expects($this->never())->method('add');

        $this->expectException(ResourceConflictException::class);
        $this->expectExceptionMessage('Fruit already existed');

        $this->controller->add($request);
    }

    public function testAddPersistsNewFruit(): void
    {
        $request = $this->createCreatePlantRequest();
        $newPlant = $this->createPlant(0, $request->goldenId, $request->name, PlantType::FRUIT, 1250);

        $this->plantRepository
            ->expects($this->once())
            ->method('findByGoldenId')
            ->with($request->goldenId)
            ->willReturn(null);
        $this->dataProcessor
            ->expects($this->once())
            ->method('convertRequestToEntity')
            ->with($request, PlantType::FRUIT)
            ->willReturn($newPlant);
        $this->collection->expects($this->once())->method('add')->with($newPlant);

        $response = $this->controller->add($request);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testUpdateThrowsNotFound(): void
    {
        $request = new UpdatePlantRequest();
        $request->goldenId = 99;
        $request->name = 'Pear';
        $request->quantity = 2.0;
        $request->unit = UnitType::KILOGRAM;

        $this->plantRepository->expects($this->once())->method('find')->with(15)->willReturn(null);
        $this->dataProcessor->expects($this->never())->method('copyFromRequestToEntity');

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Fruit not found');

        $this->controller->update(15, $request);
    }

    public function testUpdatePersistsFruit(): void
    {
        $request = new UpdatePlantRequest();
        $request->goldenId = 99;
        $request->name = 'Pear';
        $request->quantity = 2.0;
        $request->unit = UnitType::KILOGRAM;
        $plant = $this->createPlant(15, 88, 'Old Pear', PlantType::FRUIT, 500);

        $this->plantRepository->expects($this->once())->method('find')->with(15)->willReturn($plant);
        $this->dataProcessor->expects($this->once())->method('copyFromRequestToEntity')->with($plant, $request);
        $this->collection->expects($this->once())->method('add')->with($plant);

        $response = $this->controller->update(15, $request);

        $this->assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $this->collection->expects($this->once())->method('remove')->with(33);

        $response = $this->controller->delete(33);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    private function createCreatePlantRequest(): CreatePlantRequest
    {
        $request = new CreatePlantRequest();
        $request->goldenId = 123;
        $request->name = 'Apple';
        $request->quantity = 1.25;
        $request->unit = UnitType::KILOGRAM;

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

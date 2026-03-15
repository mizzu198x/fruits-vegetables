<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Service\Plant;

use App\Contract\Request\Body\BroadcastPlantRequest;
use App\Contract\Request\Body\CreatePlantRequest;
use App\Contract\Request\Body\UpdatePlantRequest;
use App\Contract\Response\Model\Plant as ResponsePlant;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Enum\UnitType;
use App\Helper\UnitConverter;
use App\Service\Plant\DataProcessor;
use App\Service\Plant\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlantServicesTest extends TestCase
{
    private UnitConverter|MockObject $unitConverter;
    private DataProcessor $dataProcessor;

    protected function setUp(): void
    {
        $this->unitConverter = $this->createMock(UnitConverter::class);
        $this->dataProcessor = new DataProcessor($this->unitConverter);
    }

    public function testCopyFromBroadcastRequestToEntity(): void
    {
        $plant = new Plant();
        $request = new BroadcastPlantRequest();
        $request->id = 200;
        $request->name = 'Pumpkin';
        $request->type = PlantType::VEGETABLE;
        $request->quantity = 2.5;
        $request->unit = UnitType::KILOGRAM;

        $this->unitConverter
            ->expects($this->once())
            ->method('toGrams')
            ->with(2.5, UnitType::KILOGRAM)
            ->willReturn(2500);

        $this->dataProcessor->copyFromRequestToEntity($plant, $request);

        $this->assertSame(200, $plant->getGoldenId());
        $this->assertSame('Pumpkin', $plant->getName());
        $this->assertSame(PlantType::VEGETABLE, $plant->getType());
        $this->assertSame(2500, $plant->getQuantity());
    }

    public function testCopyFromUpdateRequestToEntity(): void
    {
        $plant = (new Plant())
            ->setGoldenId(10)
            ->setName('Old')
            ->setType(PlantType::FRUIT)
            ->setQuantity(100);
        $request = new UpdatePlantRequest();
        $request->goldenId = 201;
        $request->name = 'Orange';
        $request->quantity = 600.0;
        $request->unit = UnitType::GRAM;

        $this->unitConverter->expects($this->once())->method('toGrams')->with(600.0, UnitType::GRAM)->willReturn(600);

        $this->dataProcessor->copyFromRequestToEntity($plant, $request);

        $this->assertSame(201, $plant->getGoldenId());
        $this->assertSame('Orange', $plant->getName());
        $this->assertSame(PlantType::FRUIT, $plant->getType());
        $this->assertSame(600, $plant->getQuantity());
    }

    public function testConvertRequestToEntity(): void
    {
        $request = new CreatePlantRequest();
        $request->goldenId = 300;
        $request->name = 'Banana';
        $request->quantity = 1.2;
        $request->unit = UnitType::KILOGRAM;

        $this->unitConverter
            ->expects($this->once())
            ->method('toGrams')
            ->with(1.2, UnitType::KILOGRAM)
            ->willReturn(1200);

        $plant = $this->dataProcessor->convertRequestToEntity($request, PlantType::FRUIT);

        $this->assertSame(300, $plant->getGoldenId());
        $this->assertSame('Banana', $plant->getName());
        $this->assertSame(PlantType::FRUIT, $plant->getType());
        $this->assertSame(1200, $plant->getQuantity());
    }

    public function testConvertEntityToResponseModel(): void
    {
        $plant = (new Plant())
            ->setId(77)
            ->setGoldenId(301)
            ->setName('Grape')
            ->setType(PlantType::FRUIT)
            ->setQuantity(2750);

        $this->unitConverter
            ->expects($this->once())
            ->method('fromGrams')
            ->with(2750, UnitType::KILOGRAM)
            ->willReturn(2.75);

        $responseModel = $this->dataProcessor->convertEntityToResponseModel($plant, UnitType::KILOGRAM);

        $this->assertInstanceOf(ResponsePlant::class, $responseModel);
        $this->assertSame(77, $responseModel->id);
        $this->assertSame(301, $responseModel->goldenId);
        $this->assertSame('Grape', $responseModel->name);
        $this->assertSame(2.75, $responseModel->quantity);
        $this->assertSame(UnitType::KILOGRAM, $responseModel->unit);
    }

    public function testCreateSearchResponse(): void
    {
        $plantA = (new Plant())
            ->setId(1)
            ->setGoldenId(10)
            ->setName('Apple')
            ->setType(PlantType::FRUIT)
            ->setQuantity(100);
        $plantB = (new Plant())
            ->setId(2)
            ->setGoldenId(20)
            ->setName('Pear')
            ->setType(PlantType::FRUIT)
            ->setQuantity(200);
        $responseModelA = new ResponsePlant();
        $responseModelB = new ResponsePlant();
        $processor = $this->createMock(DataProcessor::class);
        $factory = new ResponseFactory($processor);

        $processor->expects($this->exactly(2))
            ->method('convertEntityToResponseModel')
            ->willReturnMap([
                [$plantA, UnitType::GRAM, $responseModelA],
                [$plantB, UnitType::GRAM, $responseModelB],
            ]);

        $response = $factory->createSearchResponse([$plantA, $plantB], UnitType::GRAM);

        $this->assertSame([$responseModelA, $responseModelB], $response->items);
    }

    public function testCreateSearchResponseReturnsEmptyResponseWhenNoItemsExist(): void
    {
        $processor = $this->createMock(DataProcessor::class);
        $factory = new ResponseFactory($processor);

        $processor->expects($this->never())->method('convertEntityToResponseModel');

        $response = $factory->createSearchResponse([], UnitType::GRAM);

        $this->assertSame([], $response->items);
    }
}

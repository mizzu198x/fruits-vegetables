<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Service\Collection;

use App\Contract\Request\Query\SearchPlantRequest;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Enum\UnitType;
use App\Exception\Http\ResourceNotFoundException;
use App\Exception\Http\UnprocessableEntityException;
use App\Exception\Plant\InvalidPlantTypeException;
use App\Repository\PlantRepository;
use App\Service\Collection\CollectionResolver;
use App\Service\Collection\FruitCollection;
use App\Service\Collection\VegetableCollection;
use App\Helper\UnitConverter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionServicesTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private PlantRepository|MockObject $plantRepository;
    private UnitConverter|MockObject $unitConverter;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->plantRepository = $this->createMock(PlantRepository::class);
        $this->unitConverter = $this->createMock(UnitConverter::class);
    }

    public function testFruitType(): void
    {
        $collection = new FruitCollection($this->entityManager, $this->plantRepository, $this->unitConverter);

        $this->assertSame(PlantType::FRUIT, $collection->getType());
    }

    public function testVegetableType(): void
    {
        $collection = new VegetableCollection($this->entityManager, $this->plantRepository, $this->unitConverter);

        $this->assertSame(PlantType::VEGETABLE, $collection->getType());
    }

    public function testAddPersistsPlant(): void
    {
        $collection = new FruitCollection($this->entityManager, $this->plantRepository, $this->unitConverter);
        $plant = $this->createPlant(1, PlantType::FRUIT);

        $this->entityManager->expects($this->once())->method('persist')->with($plant);
        $this->entityManager->expects($this->once())->method('flush');

        $collection->add($plant);
    }

    public function testAddThrowsInvalidType(): void
    {
        $collection = new FruitCollection($this->entityManager, $this->plantRepository, $this->unitConverter);
        $plant = $this->createPlant(2, PlantType::VEGETABLE);

        $this->entityManager->expects($this->never())->method('persist');

        $this->expectException(InvalidPlantTypeException::class);
        $this->expectExceptionMessage('Invalid plant type. Expected "fruit".');

        $collection->add($plant);
    }

    public function testRemove(): void
    {
        $collection = new VegetableCollection($this->entityManager, $this->plantRepository, $this->unitConverter);
        $plant = $this->createPlant(4, PlantType::VEGETABLE);

        $this->plantRepository->expects($this->once())->method('find')->with(4)->willReturn($plant);
        $this->entityManager->expects($this->once())->method('remove')->with($plant);
        $this->entityManager->expects($this->once())->method('flush');

        $collection->remove(4);
    }

    public function testRemoveThrowsNotFound(): void
    {
        $collection = new VegetableCollection($this->entityManager, $this->plantRepository, $this->unitConverter);

        $this->plantRepository->expects($this->once())->method('find')->with(8)->willReturn(null);
        $this->entityManager->expects($this->never())->method('remove');

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Vegetable not found.');

        $collection->remove(8);
    }

    public function testRemoveThrowsInvalidType(): void
    {
        $collection = new FruitCollection($this->entityManager, $this->plantRepository, $this->unitConverter);
        $plant = $this->createPlant(5, PlantType::VEGETABLE);

        $this->plantRepository->expects($this->once())->method('find')->with(5)->willReturn($plant);
        $this->entityManager->expects($this->never())->method('remove');

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Fruit not found.');

        $collection->remove(5);
    }

    public function testList(): void
    {
        $collection = new FruitCollection($this->entityManager, $this->plantRepository, $this->unitConverter);
        $items = [$this->createPlant(7, PlantType::FRUIT)];

        $this->plantRepository
            ->expects($this->once())
            ->method('findByType')
            ->with(PlantType::FRUIT)
            ->willReturn($items);

        $this->assertSame($items, $collection->list());
    }

    public function testSearch(): void
    {
        $collection = new FruitCollection($this->entityManager, $this->plantRepository, $this->unitConverter);
        $request = new SearchPlantRequest();
        $request->query = 'berry';
        $request->minQty = 1.2;
        $request->maxQty = 3.4;
        $request->unit = UnitType::KILOGRAM;
        $items = [$this->createPlant(9, PlantType::FRUIT)];

        $this->unitConverter->expects($this->exactly(2))->method('toGrams')->willReturnMap([
            [1.2, UnitType::KILOGRAM, 1200],
            [3.4, UnitType::KILOGRAM, 3400],
        ]);
        $this->plantRepository
            ->expects($this->once())
            ->method('findByTypeAndSearchCriterial')
            ->with(PlantType::FRUIT, 'berry', 1200, 3400)
            ->willReturn($items);

        $this->assertSame($items, $collection->search($request));
    }

    public function testSearchWithoutConditions(): void
    {
        $collection = new VegetableCollection($this->entityManager, $this->plantRepository, $this->unitConverter);
        $request = new SearchPlantRequest();
        $request->query = null;
        $request->unit = UnitType::GRAM;

        $this->unitConverter->expects($this->never())->method('toGrams');
        $this->plantRepository
            ->expects($this->once())
            ->method('findByTypeAndSearchCriterial')
            ->with(PlantType::VEGETABLE, null, null, null)
            ->willReturn([]);

        $this->assertSame([], $collection->search($request));
    }

    public function testCollectionResolver(): void
    {
        $fruitCollection = $this->getMockBuilder(FruitCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getType'])
            ->getMock();
        $vegetableCollection = $this->getMockBuilder(VegetableCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getType'])
            ->getMock();

        $fruitCollection->method('getType')->willReturn(PlantType::FRUIT);
        $vegetableCollection->method('getType')->willReturn(PlantType::VEGETABLE);

        $resolver = new CollectionResolver([$vegetableCollection, $fruitCollection]);

        $this->assertSame($fruitCollection, $resolver->resolve(PlantType::FRUIT));
    }

    public function testCollectionResolverThrowsException(): void
    {
        $fruitCollection = $this->getMockBuilder(FruitCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getType'])
            ->getMock();
        $fruitCollection->method('getType')->willReturn(PlantType::FRUIT);

        $resolver = new CollectionResolver([$fruitCollection]);

        $this->expectException(UnprocessableEntityException::class);

        $resolver->resolve(PlantType::VEGETABLE);
    }

    private function createPlant(int $id, PlantType $type): Plant
    {
        return (new Plant())
            ->setId($id)
            ->setGoldenId($id + 100)
            ->setName('Plant '.$id)
            ->setType($type)
            ->setQuantity(250);
    }
}

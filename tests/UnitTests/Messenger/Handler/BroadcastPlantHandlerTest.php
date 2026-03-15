<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Messenger\Handler;

use App\Contract\Request\Body\BroadcastPlantRequest;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Enum\UnitType;
use App\Exception\Http\UnprocessableEntityException;
use App\Messenger\Handler\BroadcastPlantHandler;
use App\Repository\PlantRepository;
use App\Service\Collection\CollectionInterface;
use App\Service\Collection\CollectionResolver;
use App\Service\Plant\DataProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class BroadcastPlantHandlerTest extends TestCase
{
    private PlantRepository|MockObject $plantRepository;
    private CollectionResolver|MockObject $collectionResolver;
    private DataProcessor|MockObject $dataProcessor;
    private LoggerInterface|MockObject $logger;
    private BroadcastPlantHandler $handler;

    protected function setUp(): void
    {
        $this->plantRepository = $this->createMock(PlantRepository::class);
        $this->collectionResolver = $this->createMock(CollectionResolver::class);
        $this->dataProcessor = $this->createMock(DataProcessor::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new BroadcastPlantHandler(
            $this->plantRepository,
            $this->collectionResolver,
            $this->dataProcessor,
            $this->logger,
        );
    }

    public function testInvokeUpdatesExistingPlant(): void
    {
        $request = $this->createBroadcastRequest();
        $plant = (new Plant())
            ->setId(10)
            ->setGoldenId(42)
            ->setName('Old apple')
            ->setType(PlantType::FRUIT)
            ->setQuantity(500);
        $collection = $this->createMock(CollectionInterface::class);

        $this->plantRepository->expects($this->once())->method('findByGoldenId')->with(42)->willReturn($plant);
        $this->dataProcessor->expects($this->once())->method('copyFromRequestToEntity')->with($plant, $request);
        $this->collectionResolver
            ->expects($this->once())
            ->method('resolve')
            ->with(PlantType::FRUIT)
            ->willReturn($collection);
        $collection->expects($this->once())->method('add')->with($plant);
        $this->logger->expects($this->never())->method('notice');
        $this->logger->expects($this->never())->method('error');

        ($this->handler)($request);
    }

    public function testInvokeCreatesNewPlant(): void
    {
        $request = $this->createBroadcastRequest();
        $collection = $this->createMock(CollectionInterface::class);

        $this->plantRepository->expects($this->once())->method('findByGoldenId')->with(42)->willReturn(null);
        $plant = (new Plant());
        $this->dataProcessor->expects($this->once())->method('copyFromRequestToEntity')->with($plant, $request);
        $this->collectionResolver
            ->expects($this->once())
            ->method('resolve')
            ->with(PlantType::FRUIT)
            ->willReturn($collection);
        $collection->expects($this->once())->method('add')->with($plant);

        ($this->handler)($request);
    }

    public function testInvokeLogsUnprocessableEntityAndSuppressesIt(): void
    {
        $request = $this->createBroadcastRequest();
        $exception = new UnprocessableEntityException('bad broadcast');

        $this->plantRepository->expects($this->once())->method('findByGoldenId')->with(42)->willReturn(null);
        $this->dataProcessor->expects($this->once())->method('copyFromRequestToEntity')->willThrowException($exception);
        $this->collectionResolver->expects($this->never())->method('resolve');
        $this->logger->expects($this->once())->method('notice')->with($exception, $request->getContext());
        $this->logger->expects($this->never())->method('error');

        ($this->handler)($request);
    }

    public function testInvokeLogsAndRethrowsUnexpectedExceptions(): void
    {
        $request = $this->createBroadcastRequest();
        $exception = new \RuntimeException('unexpected failure');

        $this->plantRepository->expects($this->once())->method('findByGoldenId')->with(42)->willReturn(null);
        $this->dataProcessor->expects($this->once())->method('copyFromRequestToEntity')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error')->with($exception, $request->getContext());

        $this->expectExceptionObject($exception);

        ($this->handler)($request);
    }

    private function createBroadcastRequest(): BroadcastPlantRequest
    {
        $request = new BroadcastPlantRequest();
        $request->id = 42;
        $request->name = 'Apple';
        $request->type = PlantType::FRUIT;
        $request->quantity = 1.5;
        $request->unit = UnitType::KILOGRAM;

        return $request;
    }
}

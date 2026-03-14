<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use App\Contract\Request\Body\BroadcastPlantRequest;
use App\Entity\Plant;
use App\Exception\Http\UnprocessableEntityException;
use App\Repository\PlantRepository;
use App\Service\Collection\CollectionResolver;
use App\Service\Plant\DataProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class BroadcastPlantHandler
{
    public function __construct(
        private readonly PlantRepository $plantRepository,
        private readonly CollectionResolver $collectionResolver,
        private readonly DataProcessor $dataProcessor,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(BroadcastPlantRequest $request): void
    {
        try {
            $plant = $this->plantRepository->findByGoldenId($request->id);
            if (!$plant instanceof Plant) {
                $plant = new Plant();
            }

            $this->dataProcessor->copyFromRequestToEntity($plant, $request);

            $collection = $this->collectionResolver->resolve($request->type);
            $collection->add($plant);
        } catch (UnprocessableEntityException $exception) {
            $this->logger->notice($exception, $request->getContext());
        } catch (\Exception $exception) {
            $this->logger->error($exception, $request->getContext());

            throw $exception;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\Body\CreatePlantRequest;
use App\Contract\Request\Body\UpdatePlantRequest;
use App\Contract\Request\Query\SearchPlantRequest;
use App\Contract\Response\SearchPlantResponse;
use App\Entity\Plant;
use App\Enum\PlantType;
use App\Exception\Http\ResourceConflictException;
use App\Exception\Http\ResourceNotFoundException;
use App\Repository\PlantRepository;
use App\Service\Collection\FruitCollection;
use App\Service\Plant\DataProcessor;
use App\Service\Plant\ResponseFactory;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FruitController extends AbstractController
{
    public function __construct(
        private readonly PlantRepository $plantRepository,
        private readonly FruitCollection $collection,
        private readonly DataProcessor $dataProcessor,
        private readonly ResponseFactory $responseFactory,
    ) {
    }

    #[OA\Tag(name: 'fruits-operation')]
    #[OA\Query(requestBody: new OA\RequestBody(ref: new Model(type: SearchPlantRequest::class)))]
    #[OA\Response(response: 200, description: 'Fruits returned')]
    #[Security(name: 'basic')]
    #[Route('/api/v0/fruits', name: 'api_fruits_list', methods: ['GET'])]
    public function search(SearchPlantRequest $request): SearchPlantResponse
    {
        $items = $this->collection->search($request);

        return $this->responseFactory->createSearchResponse($items, $request->unit);
    }

    #[OA\Tag(name: 'fruits-operation')]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: CreatePlantRequest::class), type: 'object'))]
    #[OA\Response(response: 201, description: 'Fruit added')]
    #[OA\Response(response: 409, description: 'Fruit already existed')]
    #[Security(name: 'basic')]
    #[Route('/api/v0/fruit', name: 'api_fruits_add', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function add(CreatePlantRequest $request): Response
    {
        $plant = $this->plantRepository->findByGoldenId($request->goldenId);
        if ($plant instanceof Plant) {
            throw new ResourceConflictException('Fruit already existed');
        }

        $plant = $this->dataProcessor->convertRequestToEntity($request, PlantType::FRUIT);
        $this->collection->add($plant);

        return new Response(null, Response::HTTP_CREATED);
    }

    #[OA\Tag(name: 'fruits-operation')]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: UpdatePlantRequest::class), type: 'object'))]
    #[OA\Response(response: 202, description: 'Fruit updated')]
    #[OA\Response(response: 404, description: 'Fruit not found')]
    #[Security(name: 'basic')]
    #[Route('/api/v0/fruit/{id}', name: 'api_fruits_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(int $id, UpdatePlantRequest $request): Response
    {
        $plant = $this->plantRepository->find($id);
        if (!$plant instanceof Plant) {
            throw new ResourceNotFoundException('Fruit not found');
        }

        $this->dataProcessor->copyFromRequestToEntity($plant, $request);
        $this->collection->add($plant);

        return new Response(null, Response::HTTP_ACCEPTED);
    }

    #[OA\Tag(name: 'fruits-operation')]
    #[OA\Response(response: 204, description: 'Fruit deleted')]
    #[OA\Response(response: 404, description: 'Fruit not found')]
    #[Security(name: 'basic')]
    #[Route('/api/v0/fruit/{id}', name: 'api_fruits_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): Response
    {
        $this->collection->remove($id);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Contract\Request\Body\BroadcastPlantRequest;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class BroadcastController extends AbstractController
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    #[OA\Tag(name: 'broadcast-listener')]
    #[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: BroadcastPlantRequest::class), type: 'object'))]
    #[OA\Response(response: 202, description: 'Broadcast accepted')]
    #[Security(name: 'basic')]
    #[Route(path: '/api/v0/broadcast-listener/plant', name: 'api_broadcast_plant', methods: ['POST'], format: 'json')]
    public function broadcastPlant(BroadcastPlantRequest $request): Response
    {
        $this->messageBus->dispatch($request);

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}

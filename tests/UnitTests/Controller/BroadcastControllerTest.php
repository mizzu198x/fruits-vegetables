<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Controller;

use App\Contract\Request\Body\BroadcastPlantRequest;
use App\Controller\BroadcastController;
use App\Enum\PlantType;
use App\Enum\UnitType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class BroadcastControllerTest extends TestCase
{
    public function testBroadcast(): void
    {
        $request = new BroadcastPlantRequest();
        $request->id = 42;
        $request->name = 'Tomato';
        $request->type = PlantType::VEGETABLE;
        $request->quantity = 1.5;
        $request->unit = UnitType::KILOGRAM;

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($request)
            ->willReturn(new Envelope($request));

        $controller = new BroadcastController($messageBus);
        $response = $controller->broadcastPlant($request);

        $this->assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\EventSubscriber;

use App\Contract\Response\SearchPlantResponse;
use App\EventSubscriber\ResponseSerializerSubscriber;
use App\Kernel;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSerializerSubscriberTest extends TestCase
{
    protected SerializerInterface|MockObject $serializer;

    public function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [KernelEvents::VIEW => 'serializeObjectToJsonResponse'],
            ResponseSerializerSubscriber::getSubscribedEvents()
        );
    }

    public function testSerializeObjectToJsonResponse(): void
    {
        $testResponse = new SearchPlantResponse();
        $event = new ViewEvent(
            new Kernel('prod', false),
            new Request(),
            1,
            $testResponse,
        );
        $actionToJsonResponse = new ResponseSerializerSubscriber($this->serializer);
        $response = '{"message": "test2"}';
        $expectedResponse = new JsonResponse($response, JsonResponse::HTTP_OK, [], true);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($testResponse, 'json', null)
            ->willReturn($response);

        $actionToJsonResponse->serializeObjectToJsonResponse($event);

        $this->assertEquals($expectedResponse, $event->getResponse());
    }

    public function testSerializeObjectToJsonResponseWontBeProcessed(): void
    {
        $testResponse = '';
        $event = new ViewEvent(
            new Kernel('prod', false),
            new Request(),
            1,
            $testResponse,
        );
        $actionToJsonResponse = new ResponseSerializerSubscriber($this->serializer);

        $actionToJsonResponse->serializeObjectToJsonResponse($event);

        $this->assertEquals($testResponse, $event->getResponse());
    }
}

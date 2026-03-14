<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Contract\Response\Interface\ResponseInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSerializerSubscriber implements EventSubscriberInterface
{
    public const bool IS_JSON_FORMAT = true;

    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => 'serializeObjectToJsonResponse',
        ];
    }

    /**
     * We define a serializer convention: Every entity uses its own name as the serialization group.
     * This helps massively reduce the serialization boilerplate code and improves readability when checking
     * Entity Groups annotation.
     */
    public function serializeObjectToJsonResponse(ViewEvent $event): void
    {
        $data = $event->getControllerResult();

        if (!$data instanceof ResponseInterface) {
            return;
        }

        $serializedData = $this->serializer->serialize(
            $data,
            'json',
            null,
        );

        $event->setResponse(new JsonResponse($serializedData, $data->getHttpCode(), [], self::IS_JSON_FORMAT));
    }
}

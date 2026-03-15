<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\EventSubscriber;

use App\EventSubscriber\ApiExceptionSubscriber;
use App\Exception\Http\ApiException;
use App\Kernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriberTest extends TestCase
{
    protected LoggerInterface|MockObject $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertEquals(
            [KernelEvents::EXCEPTION => 'onKernelException'],
            ApiExceptionSubscriber::getSubscribedEvents()
        );
    }

    #[DataProvider('providerOnKernelException')]
    public function testOnKernelException(\Exception $exception, string $message, int $code, int $httpCode): void
    {
        $event = new ExceptionEvent(
            new Kernel('prod', false),
            new Request(),
            1,
            $exception,
        );

        $this->logger->expects($this->once())->method('error')->with(
            $exception->getMessage(),
            ['extra' => ['event' => $exception]],
        );
        $response = new JsonResponse(['error' => ['message' => $message, 'code' => $code]], $httpCode);

        $apiExceptionSubscriber = new ApiExceptionSubscriber($this->logger);
        $apiExceptionSubscriber->onKernelException($event);
        $this->assertEquals($response, $event->getResponse());
    }

    public function testOnKernelExceptionForNonProduction(): void
    {
        $exception = new \Exception();
        $event = new ExceptionEvent(
            new Kernel('dev', false),
            new Request([], [], [], [], [], ['APP_ENV' => 'dev']),
            1,
            $exception,
        );

        $this->logger->expects($this->once())->method('error')->with(
            $exception->getMessage(),
            ['extra' => ['event' => $exception]],
        );

        $apiExceptionSubscriber = new ApiExceptionSubscriber($this->logger);
        $apiExceptionSubscriber->onKernelException($event);
        $this->assertNull($event->getResponse());
    }

    public function testOnKernelExceptionWithErrorList(): void
    {
        $exception = ApiException::forContext(['errors' => ['error1', 'error2']]);
        $message = 'API error';
        $code = 1000001;
        $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $event = new ExceptionEvent(
            new Kernel('prod', false),
            new Request(),
            1,
            $exception,
        );

        $this->logger->expects($this->once())->method('error')->with(
            $exception->getMessage(),
            ['extra' => ['event' => $exception]],
        );
        $response = new JsonResponse(
            ['error' => ['message' => $message, 'code' => $code, 'errors' => ['error1', 'error2']]],
            $httpCode
        );

        $apiExceptionSubscriber = new ApiExceptionSubscriber($this->logger);
        $apiExceptionSubscriber->onKernelException($event);
        $this->assertEquals($response, $event->getResponse());
    }

    /**
     * @see testOnKernelException
     */
    public static function providerOnKernelException(): \Generator
    {
        yield 'random exception' => [
            new \Exception(),
            'General error',
            1000000,
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        yield 'random exception but with string code' => [
            new \Exception('aaa', 1000000),
            'General error',
            1000000,
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        yield 'http exception' => [
            new ApiException(),
            'API error',
            1000001,
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        yield 'symfony http exception' => [
            new MethodNotAllowedHttpException(['POST']),
            'Method Not Allowed',
            Response::HTTP_METHOD_NOT_ALLOWED,
            Response::HTTP_METHOD_NOT_ALLOWED,
        ];
    }
}

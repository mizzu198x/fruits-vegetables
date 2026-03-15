<?php

declare(strict_types=1);

namespace App\Tests\IntegrationTests\Controller;

use App\Tests\IntegrationTests\AbstractIntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class FruitControllerTest extends AbstractIntegrationTestCase
{
    public function testSearch(): void
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/v0/fruits',
                [
                    'query' => 'a',
                    'minQty' => '10',
                    'maxQty' => '100',
                    'unit' => 'kg',
                ],
            );

        $response = \json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(2, $response['items'][0]['goldenId']);
        $this->assertEquals('Apples', $response['items'][0]['name']);
        $this->assertEquals(20, $response['items'][0]['quantity']);
        $this->assertEquals('kg', $response['items'][0]['unit']);
    }

    public function testAddReturnsConflict(): void
    {
        $this->client->request(
            'POST',
            '/api/v0/fruit',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 2,
                    'name' => 'Grapes',
                    'quantity' => 2,
                    'unit' => 'kg',
                ]
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testAdd(): void
    {
        $this->client->request(
            'POST',
            '/api/v0/fruit',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 100,
                    'name' => 'Grapes',
                    'quantity' => 2,
                    'unit' => 'kg',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateReturnsNotFound(): void
    {
        $this->client->request(
            'PATCH',
            '/api/v0/fruit/9999',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 2,
                    'name' => 'Apples',
                    'quantity' => 20,
                    'unit' => 'kg',
                ]
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateReturnsInvalidType(): void
    {
        $this->client->request(
            'PATCH',
            '/api/v0/fruit/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 2,
                    'name' => 'Apples',
                    'quantity' => 20,
                    'unit' => 'kg',
                ]
            )
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdate(): void
    {
        $this->client->request(
            'PATCH',
            '/api/v0/fruit/2',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 2,
                    'name' => 'Apples',
                    'quantity' => 20,
                    'unit' => 'kg',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_ACCEPTED, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteReturnsNotFound(): void
    {
        $this->client->request(
            'DELETE',
            '/api/v0/fruit/9999',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
        );

        $this->assertFalse($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testDelete(): void
    {
        $id = $this->getFruitByGoldenId(100)['id'];
        $this->client->request(
            'DELETE',
            '/api/v0/fruit/'.$id,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    private function getFruitByGoldenId(int $goldenId): array
    {
        return $this->getFruits()[$goldenId];
    }

    private function getFruits(): array
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/v0/fruits',
                [],
            );

        $response = \json_decode($this->client->getResponse()->getContent(), true);
        $fruits = [];
        foreach ($response['items'] as $item) {
            $fruits[$item['goldenId']] = $item;
        }

        return $fruits;
    }
}

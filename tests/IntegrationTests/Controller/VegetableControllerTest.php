<?php

declare(strict_types=1);

namespace App\Tests\IntegrationTests\Controller;

use App\Tests\IntegrationTests\AbstractIntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class VegetableControllerTest extends AbstractIntegrationTestCase
{
    public function testSearch(): void
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/v0/vegetables',
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
        $this->assertEquals(1, $response['items'][0]['goldenId']);
        $this->assertEquals('Carrot', $response['items'][0]['name']);
        $this->assertEquals(10.922, $response['items'][0]['quantity']);
        $this->assertEquals('kg', $response['items'][0]['unit']);
    }

    public function testAddReturnsConflict(): void
    {
        $this->client->request(
            'POST',
            '/api/v0/vegetable',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 1,
                    'name' => 'Potatoes',
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
            '/api/v0/vegetable',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 200,
                    'name' => 'Potatoes',
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
            '/api/v0/vegetable/9999',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 1,
                    'name' => 'Carrot',
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
            '/api/v0/vegetable/2',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 1,
                    'name' => 'Carrot',
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
            '/api/v0/vegetable/1',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YWRtaW46YWRtaW4xMjM=',
            ],
            \json_encode(
                [
                    'goldenId' => 1,
                    'name' => 'Carrot',
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
            '/api/v0/vegetable/9999',
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
        $id = $this->getVegetableByGoldenId(200)['id'];
        $this->client->request(
            'DELETE',
            '/api/v0/vegetable/'.$id,
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

    private function getVegetableByGoldenId(int $goldenId): array
    {
        return $this->getVegetables()[$goldenId];
    }

    private function getVegetables(): array
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/v0/vegetables',
                [],
            );

        $response = \json_decode($this->client->getResponse()->getContent(), true);
        $vegetables = [];
        foreach ($response['items'] as $item) {
            $vegetables[$item['goldenId']] = $item;
        }

        return $vegetables;
    }
}

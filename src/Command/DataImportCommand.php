<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'data:import', description: 'Load data from json', hidden: false)]
class DataImportCommand extends Command
{
    private const string DATA_DIR = 'data';
    private const string PLANT_FILE = 'plant.json';
    private const string PLANT_BROADCAST_URI = '/api/v0/broadcast-listener/plant';

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly string $authUser,
        private readonly string $authPassword,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->broadcastPlants();

        return 0;
    }

    private function broadcastPlants(): void
    {
        foreach ($this->loadFile(self::PLANT_FILE) as $item) {
            $this->doRequest(self::PLANT_BROADCAST_URI, $item);
        }
    }

    private function loadFile(string $filename): array
    {
        $filePath = \sprintf(
            '%s/%s/%s',
            $this->kernel->getProjectDir(),
            self::DATA_DIR,
            $filename,
        );
        $fileContents = \file_get_contents($filePath);
        if (!$fileContents) {
            throw new \Exception(sprintf('Unable to read file %s', $filename));
        }

        $json = json_decode($fileContents, true);

        if (false === $json || null === $json) {
            throw new \Exception(sprintf('Unable to read file %s', $filename));
        }

        return $json;
    }

    private function doRequest(string $uri, array $content): void
    {
        $authHeader = 'Basic '.base64_encode($this->authUser.':'.$this->authPassword);
        $request = Request::create(
            $uri,
            'POST',
            server: [
                'HTTP_AUTHORIZATION' => $authHeader,
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode($content) ?: '',
        );
        $this->kernel->handle($request);
    }
}

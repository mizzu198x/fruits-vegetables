<?php

declare(strict_types=1);

namespace App\Tests\UnitTests\Command;

use App\Command\DataImportCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class DataImportCommandTest extends TestCase
{
    private KernelInterface|MockObject $kernel;

    protected function setUp(): void
    {
        $this->kernel = $this->createMock(KernelInterface::class);
    }

    public function testExecute(): void
    {
        $requests = [];

        $this->kernel->method('getProjectDir')->willReturn(dirname(__DIR__, 3));

        $this->kernel
            ->expects($this->exactly(20))
            ->method('handle')
            ->willReturnCallback(function (Request $request) use (&$requests): Response {
                $requests[] = $request;

                return new Response();
            });

        $command = new class ($this->kernel) extends DataImportCommand {
            public function __construct(KernelInterface $kernel)
            {
                parent::__construct($kernel, 'demo-user', 'demo-pass');
            }

            public function runExecute(InputInterface $input, OutputInterface $output): int
            {
                return $this->execute($input, $output);
            }
        };

        $result = $command->runExecute(
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
        );

        $this->assertSame(0, $result);
        $this->assertCount(20, $requests);
        $this->assertSame('/api/v0/broadcast-listener/plant', $requests[0]->getPathInfo());
        $this->assertSame('POST', $requests[0]->getMethod());
        $this->assertSame('Basic '.base64_encode('demo-user:demo-pass'), $requests[0]->headers->get('Authorization'));
        $this->assertSame('application/json', $requests[0]->headers->get('Content-Type'));
        $this->assertSame(
            '{"id":1,"name":"Carrot","type":"vegetable","quantity":10922,"unit":"g"}',
            $requests[0]->getContent()
        );
        $this->assertSame(
            '{"id":2,"name":"Apples","type":"fruit","quantity":20,"unit":"kg"}',
            $requests[1]->getContent()
        );
    }

    public function testLoadFileThrowsWhenContentsCannotBeRead(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn('/tmp/non-existent-project');

        $command = new DataImportCommand($kernel, 'user', 'pass');

        $method = new \ReflectionMethod(DataImportCommand::class, 'loadFile');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to read file missing.json');

        $method->invoke($command, 'missing.json');
    }

    public function testLoadFileThrowsWhenJsonIsInvalid(): void
    {
        $projectDir = sys_get_temp_dir().'/fruits-vegetables-data-import-'.uniqid('', true);
        mkdir($projectDir.'/data', 0777, true);
        file_put_contents($projectDir.'/data/invalid.json', '{');

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($projectDir);

        $command = new DataImportCommand($kernel, 'user', 'pass');
        $method = new \ReflectionMethod(DataImportCommand::class, 'loadFile');

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Unable to read file invalid.json');

            $method->invoke($command, 'invalid.json');
        } finally {
            unlink($projectDir.'/data/invalid.json');
            rmdir($projectDir.'/data');
            rmdir($projectDir);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\IntegrationTests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\Console\Input\StringInput;

abstract class AbstractIntegrationTestCase extends WebTestCase
{
    use WebTestAssertionsTrait;

    public static Application $application;
    public AbstractBrowser $client;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $application = new Application(self::createClient()->getKernel());
        $application->setAutoExit(false);
        self::$application = $application;
    }

    public function setUp(): void
    {
        parent::setUp();
        self::$application->run(new StringInput('d:d:c --if-not-exists'));
        self::$application->run(new StringInput('d:m:m -n'));
        self::$application->run(new StringInput('data:import'));
        $this->client = self::getContainer()->get('test.client');
    }
}

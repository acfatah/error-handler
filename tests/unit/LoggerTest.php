<?php

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testLogging()
    {
        $handler = new Fixture\Logger\MockHandler;
        $logger = new Acfatah\ErrorHandler\Logger($handler);
        $message = 'logging message';
        $logger->log('LEVEL', $message);

        $this->assertArrayHasKey('LEVEL', $handler->destination);
        $this->assertContains($message, $handler->destination['LEVEL']);
    }

    public function testAddHandler()
    {
        $default = new Fixture\Logger\MockHandler;
        $logger = new Acfatah\ErrorHandler\Logger($default);
        $debug = new Fixture\Logger\MockHandler;
        $logger->addHandler('DEBUG', $debug);
        $logger->log('LEVEL', 'log message');
        $logger->log('DEBUG', 'debug message');

        $this->assertArrayHasKey('LEVEL', $default->destination);
        $this->assertContains('log message', $default->destination['LEVEL']);
        $this->assertArrayNotHasKey('LEVEL', $debug->destination);

        $this->assertArrayHasKey('DEBUG', $default->destination);
        $this->assertContains('debug message', $default->destination['DEBUG']);

        $this->assertArrayHasKey('DEBUG', $debug->destination);
        $this->assertContains('debug message', $debug->destination['DEBUG']);
    }

    public function testSetDefaultHandler()
    {
        $default = new Fixture\Logger\MockHandler;
        $logger = new Acfatah\ErrorHandler\Logger($default);
        $other = new Fixture\Logger\MockHandler;
        $logger->setDefaultHandler($other);
        $logger->log('LEVEL', 'logging message');

        $this->assertNull($default->destination);
        $this->assertArrayHasKey('LEVEL', $other->destination);
        $this->assertContains('logging message', $other->destination['LEVEL']);
    }
}

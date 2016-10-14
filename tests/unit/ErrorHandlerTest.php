<?php

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testLogNotice()
    {
        $message = 'E_USER_NOTICE in FILE on line 0';

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('notice')
            ->with($message, []);

        $handler = new Acfatah\ErrorHandler\ErrorHandler($logger);
        $handler->errorHandler(E_USER_NOTICE, 'E_USER_NOTICE', 'FILE', 0, []);
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogWarning()
    {
        $message = 'E_USER_WARNING in FILE on line 0';

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('warning')
            ->with($message, []);

        $handler = new Acfatah\ErrorHandler\ErrorHandler($logger);
        $handler->errorHandler(E_USER_WARNING, 'E_USER_WARNING', 'FILE', 0, []);
    }

    /**
     * @runInSeparateProcess
     */
    public function testErrorConvertedToException()
    {
        $this->setExpectedException('\ErrorException', 'E_USER_ERROR');

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $handler = new Acfatah\ErrorHandler\ErrorHandler($logger);
        $handler->errorHandler(E_USER_ERROR, 'E_USER_ERROR', 'FILE', 0, []);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHandleErrorException()
    {
        $message = 'Error (Unknown): ERROR_EXCEPTION in FILE on line 0';
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with($message, []);
        $handler = new Acfatah\ErrorHandler\ErrorHandler($logger);
        $exception = new \ErrorException('ERROR_EXCEPTION', 0, 0, 'FILE', 0);
        $handler->exceptionHandler($exception);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHandleException()
    {
        $regex = '~Uncaught exception "Exception" with message "EXCEPTION" in [a-zA-Z0-9:/. \\\\_-]+:\d+\nStack trace:\n~';
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with($this->matchesRegularExpression($regex), []);
        $handler = new Acfatah\ErrorHandler\ErrorHandler($logger);
        $exception = new \Exception('EXCEPTION', 0);
        $handler->exceptionHandler($exception);
    }

    /**
     * @runInSeparateProcess
     */
    public function testErrorCallback()
    {
        $this->expectOutputString('ERROR_VIEW');

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $handler = new Acfatah\ErrorHandler\ErrorHandler($logger);
        $handler->setErrorCallback(function () {
            echo 'ERROR_VIEW';
        });
        $exception = new \Exception('EXCEPTION', 0);
        $handler->exceptionHandler($exception);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFatalHandler()
    {
        $error = [
            'type' => E_USER_ERROR,
            'message' => 'User error!',
            'file' => 'FILE',
            'line' => 0
        ];
        $message = 'Fatal Error (E_USER_ERROR): User error! in FILE on line 0';
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with($message, []);
        $handler = $this->getMock(
            'Acfatah\ErrorHandler\ErrorHandler',
            ['getLastError'],
            [$logger]
        );
        $handler
            ->method('getLastError')
            ->will($this->returnValue($error));
        $handler->fatalHandler();
    }

    /**
     * @runInSeparateProcess
     */
    public function testFatalErrorCallback()
    {
        $this->expectOutputString('ERROR_VIEW');

        $error = [
            'type' => E_USER_ERROR,
            'message' => 'User error!',
            'file' => 'FILE',
            'line' => 0
        ];
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $handler = $this->getMock(
            'Acfatah\ErrorHandler\ErrorHandler',
            ['getLastError'],
            [$logger]
        );
        $handler
            ->method('getLastError')
            ->will($this->returnValue($error));
        $handler->setErrorCallback(function () {
            echo 'ERROR_VIEW';
        });
        $handler->fatalHandler();
    }
}


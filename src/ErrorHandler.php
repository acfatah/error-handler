<?php

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright (c) 2015, Achmad F. Ibrahim
 * @link https://github.com/acfatah/error-handler
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

namespace Acfatah\ErrorHandler;

use ErrorException;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * If an error happen, this class will log error using logger and invoke a
 * callback if available.
 *
 * Internal server error(500) response code will be sent to the client and the
 * callback can be used to render appropiate view and send it to the client.
 *
 * > Note: This error handler does not handle parse error.
 *
 * For more advanced error handling and logging, see
 * {@link https://github.com/Seldaek/monolog Monolog} logger.
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
class ErrorHandler implements LoggerAwareInterface
{
    /**
     * @var boolean
     */
    private $registered;

    /**
     * @var callable
     */
    protected $errorCallback;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $reservedMemory;

    /**
     * @var array Maps the error types to string
     */
    protected static $errorMaps = [
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED'
    ];

    /**
     * @var array Fatal errors
     */
    protected static $fatalErrors = [
        E_ERROR,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_PARSE,
        E_USER_ERROR
    ];

    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, $kilobytesReservedMemory = 16)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('html_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '0');

        $this
            ->setLogger($logger)
            ->setReservedMemory($kilobytesReservedMemory);

        $this->register();
    }

    /**
     * Destructor.
     *
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->unregister();
    }

    public function register()
    {
        if (!$this->isRegistered()) {
            set_error_handler([$this, 'errorHandler']);
            set_exception_handler([$this, 'exceptionHandler']);
            register_shutdown_function([$this, 'fatalHandler']);
            $this->setRegistered(true);
        }

        return $this;
    }

    public function unregister()
    {
        if ($this->isRegistered()) {
            restore_error_handler();
            restore_exception_handler();
        }

        return $this;
    }

    /**
     * Logs notice and warning, converts php error to ErrorException.
     *
     * @param int $errorNumber
     * @param string $errorMessage
     * @param string $errorFile
     * @param int $errorLine
     * @param array $errorContext
     * @return boolean
     * @throws ErrorException
     */
    public function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine, $errorContext)
    {
        $logString = sprintf('%s in %s on line %d', $errorMessage, $errorFile, $errorLine);

        switch ($errorNumber) {
            // notices
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_STRICT:
                $this->getLogger()->notice($logString, $errorContext);
                break;
            // warnings
            case E_WARNING:
            case E_USER_WARNING:
                $this->getLogger()->warning($logString, $errorContext);
                break;
            // fatal errors
            case E_ERROR:
            case E_USER_ERROR:
            // unknown errors
            default:
                throw new ErrorException($errorMessage, 0, $errorNumber, $errorFile, $errorLine);
        } // @codeCoverageIgnore

        return true;
    }

    /**
     * Handles an uncaught exception.
     *
     * @param Exception $exception
     */
    public function exceptionHandler(Exception $exception)
    {
        if ($exception instanceof ErrorException) {
            $errorCode = $exception->getSeverity();
            $this->getLogger()->critical(sprintf(
                'Error (%s): %s in %s on line %d',
                isset(self::$errorMaps[$errorCode])? self::$errorMaps[$errorCode]: 'Unknown',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));
        } else {
            $errorCode = E_ERROR;
            $this->getLogger()->critical(sprintf(
                "Uncaught exception \"%s\" with message \"%s\" in %s:%d\nStack trace:\n%s",
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            ));
        }

        $this->invokeErrorCallback($exception);
    }

    /**
     * Handles the fatal error.
     */
    public function fatalHandler()
    {
        $this->setReservedMemory(0);
        $error = $this->getLastError();
        if ($error && in_array($error['type'], self::$fatalErrors)) {
            $this->getLogger()->critical(sprintf(
                'Fatal Error (%s): %s in %s on line %d',
                isset(self::$errorMaps[$error['type']])? self::$errorMaps[$error['type']]: 'Unknown',
                $error['message'],
                $error['file'],
                $error['line']
            ));
            if (null !== $this->getErrorCallback()) {
                call_user_func(
                    $this->getErrorCallback(),
                    new ErrorException(
                        $error['message'],
                        0,
                        $error['type'],
                        $error['file'],
                        $error['line']
                    )
                );
            }
        }
    }

    /**
     * Sets a callback to be executed when an error exists.
     *
     * The callback can be used to render appropiate view to the client.
     * An exception will be passed as an argument to the callback.
     *
     * @param callable $callback
     * @return \Library\Core\ErrorHandler
     */
    public function setErrorCallback(callable $callback)
    {
        $this->errorCallback = $callback;

        return $this;
    }

    /**
     * Gets the error handler callback.
     *
     * @return callable
     */
    public function getErrorCallback()
    {
        return $this->errorCallback;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Gets the logger.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets the reserved memory in kilobytes.
     *
     * @param int $kilobytes
     */
    public function setReservedMemory($kilobytes)
    {
        $this->reservedMemory = str_repeat(' ', 1024 * intval($kilobytes));

        return $this;
    }

    /**
     * Simply returns `error_get_last()`.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function getLastError()
    {
        return error_get_last();
    }

    /**
     * Invokes the error callback.
     *
     * @param type $exception
     */
    protected function invokeErrorCallback($exception)
    {
        // sets appropiate header
        $protocol = isset($_SERVER['SERVER_PROTOCOL'])? $_SERVER['SERVER_PROTOCOL']: 'HTTP/1.1';
        header("$protocol 500 Internal Server Error");
        http_response_code(500);
        // invoke the callback
        if (null !== $this->getErrorCallback()) {
            call_user_func($this->getErrorCallback(), $exception);
        }
    }

    /**
     * Registered setter.
     *
     * @param boolean $registered
     * @return static
     */
    protected function setRegistered($registered)
    {
        $this->registered = boolval($registered);

        return $this;
    }

    /**
     * Checks whether the handler is registered.
     *
     * @return boolean
     */
    protected function isRegistered()
    {
        return $this->registered;
    }
}

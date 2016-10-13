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

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Acfatah\ErrorHandler\Logger\HandlerInterface;
use Acfatah\ErrorHandler\Logger\Handler\FileHandler;
use Acfatah\ErrorHandler\Logger\Formatter\DefaultFormatter;

/**
 * Logs data using handler(s).
 *
 * This class implements [`\Psr\Log\LoggerInterface`][psr] class.
 *
 * For more advanced error handling and logging, see
 * {@link https://github.com/Seldaek/monolog Monolog} logger.
 *
 * [psr]: https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
class Logger extends AbstractLogger implements LoggerInterface
{
    /**
     * @var \Acfatah\ErrorHandler\Logger\HandlerInterface
     */
    protected $defaultHandler;

    /**
     * @var \Acfatah\ErrorHandler\Logger\HandlerInterface[]
     */
    protected $handlers;

    /**
     * Constructor.
     *
     * @param \Acfatah\ErrorHandler\Logger\HandlerInterface $defaultHandler Default log handler
     */
    public function __construct(HandlerInterface $defaultHandler = null)
    {
        if (is_null($defaultHandler)) {
            $this->setDefaultHandler(
                new FileHandler(new DefaultFormatter, ini_get('error_log'))
            );

            return;
        }

        $this->setDefaultHandler($defaultHandler);
    }

    /**
     * Add an additional handler to a log level.
     *
     * @param string $level See [`\Psr\Log\LogLevel`][psr].
     *     [psr]: https://github.com/php-fig/log/blob/master/Psr/Log/LogLevel.php
     * @param \Acfatah\ErrorHandler\Logger\HandlerInterface $handler Log handler
     */
    public function addHandler($level, HandlerInterface $handler)
    {
        $this->handlers[$level][] = $handler;

        return $this;
    }

    /**
     * Logs the error level, message and context.
     *
     * This method implements [`\Psr\Log\LoggerInterface::log()`][psr].
     *
     * [psr]: https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php#L122
     *
     * @param mixed $level Error level
     * @param string $message Error message
     * @param array $context Error context if any
     */
    public function log($level, $message, array $context = [])
    {
        $this->defaultHandler->log($level, $message, $context);

        if (isset($this->handlers[$level])) {
            foreach ($this->handlers[$level] as $logger) {
                $logger->log($level, $message, $context);
            }

        }
    }

    /**
     * Sets the default log handler.
     *
     * @param \Acfatah\ErrorHandler\Logger\HandlerInterface $defaultHandler Default log handler
     * @return static
     */
    public function setDefaultHandler(HandlerInterface $defaultHandler)
    {
        $this->defaultHandler = $defaultHandler;

        return $this;
    }

    /**
     * Gets the default log handler.
     *
     * @return \Acfatah\ErrorHandler\Logger\HandlerInterface
     */
    public function getDefaultHandler()
    {
        return $this->defaultHandler;
    }
}

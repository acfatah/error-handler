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

namespace Acfatah\ErrorHandler\Logger\Handler;

use Acfatah\ErrorHandler\Logger\FormatterInterface;
use Acfatah\ErrorHandler\Logger\HandlerInterface;

/**
 * Uses php **error_log** function with option **1** to send error message to email.
 *
 * Read more about `error_log` function at {@link http://php.net/manual/en/function.error-log.php php.net}.
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 *
 * @codeCoverageIgnore
 */
class EmailHandler implements HandlerInterface
{
    /**
     * @var string Email to send the error log message
     */
    private $email;

    /**
     * @var string
     */
    private $extraHeaders;

    /**
     * @var Acfatah\ErrorHandler\Logger\FormatterInterface
     */
    private $formatter;

    /**
     * Constructor.
     *
     * @param string $email
     */
    public function __construct(FormatterInterface $formatter, $email, $extraHeaders = null)
    {
        $this->formatter = $formatter;
        $this->email = $email;
        $this->extraHeaders = $extraHeaders;
    }

    /**
     * Sets the extra headers to send.
     *
     * @param string $extraHeaders
     * @return Acfatah\ErrorHandler\Logger\Handler\EmailHandler
     */
    public function setExtraHeaders($extraHeaders)
    {
        $this->extraHeaders = $extraHeaders;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, $context)
    {
        if (isset($this->extraHeaders)) {
            return error_log(
                $this->formatter->format($level, $message, $context),
                1,
                $this->email,
                $this->extraHeaders
            );
        }
        return error_log($this->formatter->format($level, $message, $context), 1, $this->email);
    }
}

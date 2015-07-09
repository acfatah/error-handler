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

namespace Acfatah\ErrorHandler\Logger;

/**
 * Describes a log handler.
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
interface HandlerInterface
{
    /**
     * Implements the logging.
     * @param string $level See [`\Psr\Log\LogLevel`][psr]
     *     [psr]: https://github.com/php-fig/log/blob/master/Psr/Log/LogLevel.php
     * @param string $message Error message
     * @param array $context Error context
     */
    public function log($level, $message, $context);
}

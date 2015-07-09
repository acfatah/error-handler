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

namespace Acfatah\ErrorHandler\Logger\Formatter;

use DateTime;
use DateTimeZone;
use Acfatah\ErrorHandler\Logger\FormatterInterface;

/**
 * Prepends date, time and log level to the log message.
 *
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 */
class DefaultFormatter implements FormatterInterface
{
    /**
     * @var string Default time format
     */
    private $timeFormat = 'Y-m-d H:i:s T';

    /**
     * {@inheritdoc}
     */
    public function format($level, $message, array $context = [])
    {
        if (!empty($context)) {
            $message .= PHP_EOL . 'Context: ' . PHP_EOL . '    '
                . str_replace("\n", "\n    ", var_export($context, true));
        }
        return sprintf('[%s] [%s] %s', $this->getTime(), strtoupper($level), $message) . PHP_EOL;
    }

    /**
     * Replaces the time format.
     *
     * Default time format is: `Y-m-d H:i:s T`
     *
     * @param string $timeFormat Time format
     */
    public function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;

        return $this;
    }

    /**
     * Gets the formatted current time.
     *
     * @return string
     */
    protected function getTime()
    {
        $timeZone = date_default_timezone_get();
        $date = new DateTime(null, new DateTimeZone($timeZone));
        return $date->format($this->timeFormat);
    }
}

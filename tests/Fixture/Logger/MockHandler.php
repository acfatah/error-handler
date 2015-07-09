<?php

namespace Fixture\Logger;

use Acfatah\ErrorHandler\Logger\HandlerInterface;

class MockHandler implements HandlerInterface
{
    public $destination;

    public function log($level, $message, $context)
    {
        $this->destination[$level][] = $message;
    }
}

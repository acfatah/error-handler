<?php

class DefaultFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $formatter = new Acfatah\ErrorHandler\Logger\Formatter\DefaultFormatter;
        $context = [
            'FOO' => 'foo',
            'BAR' => 'bar'
        ];
        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} UTC\] \[\w+\] .+\n.*/';

        $this->assertRegExp($pattern, $formatter->format('DEBUG', 'Some error message...', $context));
    }

    public function testSetTimeFormat()
    {
        $formatter = new Acfatah\ErrorHandler\Logger\Formatter\DefaultFormatter;
        $formatter->setTimeFormat('d m Y H:i:s T');

        $pattern = '/\[\d{2} \d{2} \d{4} \d{2}:\d{2}:\d{2} UTC\] \[\w+\] .+\n.*/';
        $this->assertRegExp($pattern, $formatter->format('DEBUG', 'Some error message...'));
    }
}

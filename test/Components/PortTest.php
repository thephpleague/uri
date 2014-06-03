<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Port;

class PortTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testPortSetter()
    {
        $host = new Port('toto');
    }
}

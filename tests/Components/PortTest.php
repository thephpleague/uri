<?php

namespace League\Url\Test\Components;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Port;

/**
 * @group components
 */
class PortTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testPortSetter()
    {
        $port = new Port(new Port(443));
        $this->assertSame(443, $port->get());
        $port = new Port('toto');
    }
}

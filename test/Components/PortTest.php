<?php

namespace League\Url\test;

use PHPUnit_Framework_TestCase;
use League\Url\Components\Port;
use League\Url\Components\Scheme;

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

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testPortExchange()
    {
        $port = new Port;
        $new_port = new Port(443);

        $port->exchange($new_port);
        $this->assertSame(443, $port->get());

        $scheme = new Scheme('http');
        $port->exchange($scheme);
    }
}

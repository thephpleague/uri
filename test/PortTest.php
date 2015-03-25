<?php

namespace League\Url\Test\Components;

use League\Url\Port;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class PortTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPortSetter()
    {
        $port = new Port(new Port(443));
        $this->assertSame(443, $port->get());
        new Port('toto');
    }

    public function testGetUriComponent()
    {
        $port = new Port(80);
        $this->assertSame(':80', $port->getUriComponent());
    }

    public function testGetUriComponentWithEmptyPort()
    {
        $port = new Port();
        $this->assertSame('', $port->getUriComponent());
    }
}

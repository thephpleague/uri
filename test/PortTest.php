<?php

namespace League\Url\Test\Components;

use League\Url\Port;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class PortTest extends PHPUnit_Framework_TestCase
{
    public function testPortSetter()
    {
        $port = new Port(new Port(443));
        $this->assertSame(443, $port->get());
    }

    public function invalidPortProvider()
    {
        return [
            ["toto"],
            ["-23"],
            ["10000000"],
            ["0"],
        ];
    }

    /**
     * @param $port
     *
     * @dataProvider invalidPortProvider
     *
     * @expectedException \InvalidArgumentException
     */
    public function testFailedPort($port)
    {
        new Port($port);
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

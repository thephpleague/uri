<?php

namespace League\Url\Test\Components;

use League\Url\Port;
use League\Url\Scheme;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class PortTest extends PHPUnit_Framework_TestCase
{
    public function testPortSetter()
    {
        $port = new Port(new Port(443));
        $this->assertSame('443', $port->__toString());
    }

    /**
     * @param  $input
     * @param  $expected
     * @dataProvider toIntProvider
     */
    public function testToInt($input, $expected)
    {
        $this->assertSame($expected, (new Port($input))->toInt());
    }

    public function toIntProvider()
    {
        return [
            ['443', 443],
            [null, null],
            [23, 23]
        ];
    }

    public function invalidPortProvider()
    {
        return [
            [new \StdClass],
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

    /**
     * @param  $input
     * @param  $expected
     * @dataProvider getUriComponentProvider
     */
    public function testGetUriComponent($input, $expected)
    {
        $this->assertSame($expected, (new Port($input))->getUriComponent());
    }

    public function getUriComponentProvider()
    {
        return [
            ['443', ':443'],
            [null, ''],
            [23, ':23']
        ];
    }

    /**
     * @param  $input
     * @param  $expected
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($input, $expected)
    {
        $this->assertSame($expected, (new Port($input))->isEmpty());
    }

    public function isEmptyProvider()
    {
        return [
            ['443', false],
            [null, true],
            [23, false]
        ];
    }

    /**
     * @param  $port
     * @param  $expected
     * @dataProvider schemeProvider
     */
    public function testGetDefaultSchemes($port, $expected)
    {
        $this->assertEquals($expected, (new Port($port))->getStandardSchemes());
    }

    public function schemeProvider()
    {
        return [
            ['443', [new Scheme('https'), new Scheme('wss')]],
            [null, []],
            [23, []],
            ['443', [new Scheme('https'), new Scheme('wss')]],
        ];
    }
}

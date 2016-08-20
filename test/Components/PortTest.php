<?php

namespace League\Uri\Test\Components;

use League\Uri\Components\Port;
use League\Uri\Test\AbstractTestCase;

/**
 * @group port
 */
class PortTest extends AbstractTestCase
{
    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $component = new Port(42);
        $this->assertInternalType('array', $component->__debugInfo());
        ob_start();
        var_dump($component);
        $res = ob_get_clean();
        $this->assertContains($component->__toString(), $res);
        $this->assertContains('port', $res);
    }

    public function testPortSetter()
    {
        $port = new Port(new Port(443));
        $this->assertSame('443', $port->__toString());
    }

    /**
     * @param  $input
     * @param  $expected
     * @dataProvider getToIntProvider
     */
    public function testToInt($input, $expected)
    {
        $this->assertSame($expected, (new Port($input))->toInt());
    }

    public function getToIntProvider()
    {
        return [
            ['443', 443],
            [null, null],
            [23, 23],
        ];
    }

    public function invalidPortProvider()
    {
        return [
            'empty string' => [''],
            'string' => ['toto'],
            'invalid port number too low' => ['-23'],
            'invalid port number too high' => ['10000000'],
            'invalid port number' => ['0'],
            'bool' => [true],
            'Std Class' => [(object) 'foo'],
            'float' => [1.2],
            'array' => [['foo']],
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
            [23, ':23'],
        ];
    }
}

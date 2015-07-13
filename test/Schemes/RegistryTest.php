<?php

namespace League\Uri\Test\Schemes;

use League\Uri\Port;
use League\Uri\Schemes\Registry;
use PHPUnit_Framework_TestCase;

/**
 * @group scheme
 */
class RegistryTest extends PHPUnit_Framework_TestCase
{

    public function testCountable()
    {
        $registry = new Registry();
        $this->assertCount($registry->count(), $registry);
    }

    public function testIterator()
    {
        $this->assertInstanceOf('\Iterator', (new Registry())->getIterator());
    }

    public function testRegister()
    {
        $registry = new Registry(['yolo' => 2020]);
        $this->assertTrue($registry->hasKey('yolo'));
    }

    public function testRegisterSchemeWithoutHost()
    {
        $registry = new Registry();
        $this->assertFalse($registry->hasKey('yolo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetPortOnUnknownScheme()
    {
        $registry = new Registry();
        $registry->getPort('yolo');
    }

    public function testOffsets()
    {
        $registry = new Registry();
        $this->assertSame(array_keys($registry->toArray()), $registry->keys());
    }

    public function testOffsetsWithArguments()
    {
        $registry = new Registry();
        $this->assertSame(['http', 'ws'], $registry->keys(80));
    }

    /**
     * @param $scheme
     * @param $port
     *
     * @dataProvider newregisterProvider
     * @expectedException \InvalidArgumentException
     */
    public function testAddFailed($scheme, $port)
    {
        new Registry([$scheme => $port]);
    }

    public function newregisterProvider()
    {
        return [
            'invalid host' => ['yóló', null],
            'invalid ports' => ['yolo', 'coucou'],
        ];
    }

    /**
     * @param $scheme
     * @param $expected
     *
     * @dataProvider portProvider
     */
    public function testGetDefaultPorts($scheme, $expected)
    {
        $this->assertEquals($expected, (new Registry())->getPort($scheme));
    }

    public function portProvider()
    {
        return [
            ['http', new Port(80)],
        ];
    }

    /**
     * @param $input
     * @param $scheme
     * @param $port
     * @dataProvider validMergeValue
     */
    public function testMerge($input, $scheme, $port)
    {
        $registry = (new Registry())->merge($input);
        $this->assertEquals($port, $registry->getPort($scheme));
    }

    public function validMergeValue()
    {
        return [
            [["yolo" => 2020], "yolo", new Port(2020)],
            [["YOLo" => 2020], "yolo", new Port(2020)],
            [["http" => 81], "http", new Port(81)],
            [new Registry(), "http", new Port(80)],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailedMerge()
    {
        (new Registry())->merge("coucou");
    }

    /**
     * @param $input
     * @param $scheme
     * @dataProvider validWithoutValue
     */
    public function testWithout($input, $scheme)
    {
        $registry = (new Registry())->without($input);
        $this->assertFalse($registry->hasKey($scheme));
    }

    public function validWithoutValue()
    {
        return [
            [['http'], 'HtTp'],
            [['YolO'], 'yOlO'],
            [function ($value) {
                return strpos($value, 's') !== false;
            }, 'wss']
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithoutFailed()
    {
        (new Registry())->without("foo");
    }
}

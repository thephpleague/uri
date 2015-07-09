<?php

namespace League\Uri\Test\Scheme;

use League\Uri\Port;
use League\Uri\Scheme;
use League\Uri\Services;
use PHPUnit_Framework_TestCase;

/**
 * @group scheme
 */
class RegistryTest extends PHPUnit_Framework_TestCase
{

    public function testCountable()
    {
        $registry = new Scheme\Registry();
        $this->assertCount($registry->count(), $registry);
    }

    public function testIterator()
    {
        $this->assertInstanceOf('\Iterator', (new Scheme\Registry())->getIterator());
    }

    public function testRegister()
    {
        $registry = new Scheme\Registry(['yolo' => 2020]);
        $this->assertTrue($registry->hasKey('yolo'));
    }

    public function testRegisterSchemeWithoutHost()
    {
        $registry = new Scheme\Registry();
        $this->assertFalse($registry->hasKey('yolo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetPortOnUnknownScheme()
    {
        $registry = new Scheme\Registry();
        $registry->getPort('yolo');
    }

    public function testOffsets()
    {
        $registry = new Scheme\Registry();
        $this->assertSame(array_keys($registry->toArray()), $registry->keys());
    }

    public function testOffsetsWithArguments()
    {
        $registry = new Scheme\Registry();
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
        new Scheme\Registry([$scheme => $port]);
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
        $this->assertEquals($expected, (new Scheme\Registry())->getPort($scheme));
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
        $registry = (new Scheme\Registry())->merge($input);
        $this->assertEquals($port, $registry->getPort($scheme));
    }

    public function validMergeValue()
    {
        return [
            [["yolo" => 2020], "yolo", new Port(2020)],
            [["YOLo" => 2020], "yolo", new Port(2020)],
            [["http" => 81], "http", new Port(81)],
            [new Scheme\Registry(), "http", new Port(80)],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailedMerge()
    {
        (new Scheme\Registry())->merge("coucou");
    }

    /**
     * @param $input
     * @param $scheme
     * @dataProvider validWithoutValue
     */
    public function testWithout($input, $scheme)
    {
        $registry = (new Scheme\Registry())->without($input);
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
        (new Scheme\Registry())->without("foo");
    }
}

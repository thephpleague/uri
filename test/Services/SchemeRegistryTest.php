<?php

namespace League\Uri\Test\Services;

use League\Uri\Port;
use League\Uri\Scheme;
use League\Uri\Services;
use PHPUnit_Framework_TestCase;

/**
 * @group scheme
 */
class SchemeRegistryTest extends PHPUnit_Framework_TestCase
{

    public function testCountable()
    {
        $registry = new Services\SchemeRegistry();
        $this->assertCount($registry->count(), $registry);
    }

    public function testIterator()
    {
        $this->assertInstanceOf('\Iterator', (new Services\SchemeRegistry())->getIterator());
    }

    public function testRegister()
    {
        $registry = new Services\SchemeRegistry(['yolo' => 2020]);
        $this->assertTrue($registry->hasOffset('yolo'));
    }

    public function testRegisterSchemeWithoutHost()
    {
        $registry = new Services\SchemeRegistry();
        $this->assertFalse($registry->hasOffset('yolo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetPortOnUnknownScheme()
    {
        $registry = new Services\SchemeRegistry();
        $registry->getPort('yolo');
    }

    public function testOffsets()
    {
        $registry = new Services\SchemeRegistry();
        $this->assertSame(array_keys($registry->toArray()), $registry->offsets());
    }

    public function testOffsetsWithArguments()
    {
        $registry = new Services\SchemeRegistry();
        $this->assertSame(['http', 'ws'], $registry->offsets(80));
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
        new Services\SchemeRegistry([$scheme => $port]);
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
        $this->assertEquals($expected, (new Services\SchemeRegistry())->getPort($scheme));
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
        $registry = (new Services\SchemeRegistry())->merge($input);
        $this->assertEquals($port, $registry->getPort($scheme));
    }

    public function validMergeValue()
    {
        return [
            [["yolo" => 2020], "yolo", new Port(2020)],
            [["YOLo" => 2020], "yolo", new Port(2020)],
            [["http" => 81], "http", new Port(81)],
            [new Services\SchemeRegistry(), "http", new Port(80)],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailedMerge()
    {
        (new Services\SchemeRegistry())->merge("coucou");
    }

    /**
     * @param $input
     * @param $scheme
     * @dataProvider validWithoutValue
     */
    public function testWithout($input, $scheme)
    {
        $registry = (new Services\SchemeRegistry())->without($input);
        $this->assertFalse($registry->hasOffset($scheme));
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
        (new Services\SchemeRegistry())->without("foo");
    }
}

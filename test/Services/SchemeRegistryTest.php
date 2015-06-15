<?php

namespace League\Url\Test\Services;

use League\Url\Port;
use League\Url\Scheme;
use League\Url\Services;
use PHPUnit_Framework_TestCase;

/**
 * @group scheme
 */
class SchemeRegistryTest extends PHPUnit_Framework_TestCase
{

    public function testCountable()
    {
        $registry = new Services\SchemeRegistry();
        $this->assertCount(7, $registry);
    }

    public function testIterator()
    {
        $this->assertInstanceOf('\Iterator', (new Services\SchemeRegistry())->getIterator());
    }

    public function testRegister()
    {
        $registry = new Services\SchemeRegistry(['yolo' => 2020]);
        $registry->add('yolo', 2020);
        $this->assertTrue($registry->has('yolo'));
    }

    public function testRegisterSchemeWithoutHost()
    {
        $registry = new Services\SchemeRegistry();
        $registry->add('yolo');
        $this->assertTrue($registry->has('yolo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetStandardPortOnUnknownScheme()
    {
        $registry = new Services\SchemeRegistry();
        $registry->getStandardPorts('yolo');
    }


    public function testRemoveCustomScheme()
    {
        $registry = new Services\SchemeRegistry();
        $registry->add('yolo');
        $this->assertTrue($registry->has('yolo'));
        $registry->remove('yolo');
        $this->assertFalse($registry->has('yolo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveDefaultSchemeFailed()
    {
        (new Services\SchemeRegistry())->remove('http');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDetectStandardPortFailed()
    {
        (new Services\SchemeRegistry())->isStandardPort('yolo', 80);
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
        (new Services\SchemeRegistry())->add($scheme, $port);
    }

    public function newregisterProvider()
    {
        return [
            'invalid host' => ['yóló', null],
            'invalid ports' => ['yolo', 'coucou'],
            'defined scheme' => ['http', 81]
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
        $this->assertEquals($expected, (new Services\SchemeRegistry())->getStandardPorts($scheme));
    }

    public function portProvider()
    {
        return [
            ['http', [new Port(80)]],
            ['', []],
            ['ftps', [new Port(989), new Port(990)]],
        ];
    }

    /**
     * @param $scheme
     * @param $port
     * @param $expected
     *
     * @dataProvider hasStandardProvider
     */
    public function testHasStandardPort($scheme, $port, $expected)
    {
        $this->assertSame($expected, (new Services\SchemeRegistry())->isStandardPort($scheme, $port));
    }

    public function hasStandardProvider()
    {
        return [
            ['http', 80, true],
            ['http', null, true],
            ['ftp', 80, false],
            ['ws', 80, true],
            ['', 80, false],
        ];
    }
}

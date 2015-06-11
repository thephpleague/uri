<?php

namespace League\Url\Test;

use League\Url\Port;
use League\Url\Scheme;
use League\Url\Utilities;
use PHPUnit_Framework_TestCase;

/**
 * @group scheme
 */
class SchemeRegistryTest extends PHPUnit_Framework_TestCase
{

    public function testCountable()
    {
        $registry = new Utilities\SchemeRegistry();
        $this->assertCount(7, $registry);
    }

    public function testIterator()
    {
        $this->assertInstanceOf('\Iterator', (new Utilities\SchemeRegistry())->getIterator());
    }

    public function testRegister()
    {
        $registry = new Utilities\SchemeRegistry(['yolo' => 2020]);
        $registry->add('yolo', 2020);
        $scheme   = new Scheme('yolo', $registry);
        $this->assertTrue($scheme->hasStandardPort(2020));
    }

    public function testRegisterSchemeWithoutHost()
    {
        $registry = new Utilities\SchemeRegistry();
        $registry->add('yolo');
        $scheme = new Scheme('yolo', $registry);
        $this->assertSame([], $scheme->getStandardPorts());
    }

    public function testGetStandardPortOnUnknownScheme()
    {
        $registry = new Utilities\SchemeRegistry();
        $this->assertSame([], $registry->getStandardPorts('yolo'));
    }


    public function testRemoveCustomScheme()
    {
        $registry = new Utilities\SchemeRegistry();
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
        (new Utilities\SchemeRegistry())->remove('http');
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
        (new Utilities\SchemeRegistry())->add($scheme, $port);
    }

    public function newregisterProvider()
    {
        return [
            'invalid host' => ['yóló', null],
            'invalid ports' => ['yolo', 'coucou'],
            'defined scheme' => ['http', 81]
        ];
    }
}

<?php

namespace Bakame\Url\Components;

class HostTest extends \PHPUnit_Framework_TestCase
{

    private $host;

    public function setUp()
    {
        $path = 'foo.example.com';
        $this->host = new Host($path);
    }

    public function testConstructor()
    {
        $res = $this->host->all();
        $this->assertInternalType('array', $res);
        $this->assertCount(3, $res);
        $this->assertSame(array('foo', 'example', 'com'), $res);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetTooLongParts()
    {
        $this->host->set(array(
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf',
            'fsdfqffqsfqfqsfqsfqsfqsfqfqfqsfqsdfqsdf'
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetTooManyPars()
    {
        $test = array_fill(0, 130, 'foo');
        $this->host->set($test);
    }
}

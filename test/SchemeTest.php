<?php

namespace League\Url\Test;

use League\Url\Port;
use League\Url\Scheme;
use PHPUnit_Framework_TestCase;

/**
 * @group components
 */
class SchemeTest extends PHPUnit_Framework_TestCase
{

    public function testWithValue()
    {
        $scheme = new Scheme('ftp');
        $http_scheme = $scheme->withValue('HTTP');
        $this->assertSame('http', $http_scheme->__toString());
        $this->assertSame('http:', $http_scheme->getUriComponent());
    }

    public function testEmptyScheme()
    {
        $this->assertEmpty((new Scheme())->__toString());
    }

    /**
     * @param $scheme
     * @param $toString
     * @dataProvider validSchemeProvider
     */
    public function testValidScheme($scheme, $toString)
    {
        $this->assertSame($toString, (new Scheme($scheme))->__toString());
    }

    public function validSchemeProvider()
    {
        return [
            ['', ''],
            ['ftp', 'ftp'],
            ['HtTps', 'https'],
            ['sSh', 'ssh'],
        ];
    }

    public function invalidSchemeProvider()
    {
        return [
            ['in,valid'],
            ['123'],
        ];
    }

    /**
     * @param  $scheme
     * @dataProvider invalidSchemeProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidScheme($scheme)
    {
        new Scheme($scheme);
    }

    public function testSameValueAs()
    {
        $scheme  = new Scheme();
        $scheme1 = new Scheme('https');
        $this->assertFalse($scheme->sameValueAs($scheme1));
        $newscheme = $scheme1->withValue(null);
        $this->assertTrue($scheme->sameValueAs($newscheme));
        $this->assertSame('', $newscheme->getUriComponent());
    }

    /**
     * @param  $input
     * @param  $expected
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($input, $expected)
    {
        $this->assertSame($expected, (new Scheme($input))->isEmpty());
    }

    public function isEmptyProvider()
    {
        return [
            ['yes', false],
            [null, true],
            ['', true]
        ];
    }

    /**
     * @param  $scheme
     * @param  $expected
     * @dataProvider portProvider
     */
    public function testGetDefaultPorts($scheme, $expected)
    {
        $this->assertEquals($expected, (new Scheme($scheme))->getStandardPorts());
    }

    public function portProvider()
    {
        return [
            ['http', [new Port(80)]],
            ['', []],
            ['ftps', [new Port(989), new Port(990)]],
            ['svn+ssh', [new Port(22)]],
        ];
    }


    /**
     * @param  $scheme
     * @param  $port
     * @param  $expected
     * @dataProvider useStandardProvider
     */
    public function testUseStandardPort($scheme, $port, $expected)
    {
        $this->assertSame($expected, (new Scheme($scheme))->useStandardPort($port));
    }

    public function useStandardProvider()
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

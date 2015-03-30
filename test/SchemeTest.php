<?php

namespace League\Url\Test;

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
        $this->assertSame('http', $http_scheme->get());
        $this->assertSame('http:', $http_scheme->getUriComponent());
    }

    public function testEmptyScheme()
    {
        $scheme = new Scheme();
        $this->assertNull($scheme->get());
    }

    /**
     * @param $scheme
     * @param $toString
     * @dataProvider validSchemeProvider
     */
    public function testValidScheme($scheme, $toString)
    {
        $scheme = new Scheme($scheme);
        $this->assertSame($toString, $scheme->__toString());
    }

    public function validSchemeProvider()
    {
        return [
            ['', ''],
            ['ftp', 'ftp'],
            ['HtTps:', 'https'],
        ];
    }

    public function invalidSchemeProvider()
    {
        return [
            ['in,valid'],
            ['123'],
            ['scheme://']
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
}

<?php

namespace League\Uri\Test;

use League\Uri\Port;
use League\Uri\Scheme;
use League\Uri\Utilities;
use PHPUnit_Framework_TestCase;

/**
 * @group scheme
 */
class SchemeTest extends PHPUnit_Framework_TestCase
{

    public function testWithValue()
    {
        $scheme = new Scheme('ftp');
        $http_scheme = $scheme->modify('HTTP');
        $this->assertSame('http', $http_scheme->__toString());
        $this->assertSame('http:', $http_scheme->getUriComponent());
    }

    public function testEmptyScheme()
    {
        $this->assertEmpty((new Scheme())->__toString());
    }

    /**
     * @dataProvider validSchemeProvider
     */
    public function testValidScheme($scheme, $toString)
    {
        $this->assertSame($toString, (new Scheme($scheme))->__toString());
    }

    /**
     * @dataProvider validSchemeProvider
     */
    public function testGetLiteral($scheme, $toString)
    {
        $this->assertSame($toString, (new Scheme($scheme))->getLiteral());
    }

    public function validSchemeProvider()
    {
        return [
            ['', ''],
            ['ftp', 'ftp'],
            ['HtTps', 'https'],
            ['wSs', 'wss'],
            ['telnEt', 'telnet'],
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

    public function invalidSchemeProvider()
    {
        return [
            'invalid char'         => ['in,valid'],
            'integer like string'  => ['123'],
            'bool'                 => [true],
            'Std Class'            => [(object) 'foo'],
            'null'                 => [null],
            'float'                => [1.2],
            'array'                =>[['foo']],
        ];
    }

    public function testSameValueAs()
    {
        $scheme  = new Scheme();
        $scheme1 = new Scheme('https');
        $this->assertFalse($scheme->sameValueAs($scheme1));
        $newscheme = $scheme1->modify('');
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
            ['ftp', false],
            ['', true]
        ];
    }
}

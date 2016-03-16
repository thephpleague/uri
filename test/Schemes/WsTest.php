<?php

namespace League\Uri\Test\Schemes;

use League\Uri\Schemes\Ws;
use PHPUnit_Framework_TestCase;

/**
 * @group ws
 */
class WsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validUrlArray
     * @param $expected
     * @param $input
     */
    public function testCreateFromString($expected, $input)
    {
        $uri = Ws::createFromString($input);
        $this->assertSame($expected, $uri->__toString());
        eval('$var = '.var_export($uri, true).';');
        $this->assertEquals($var, $uri);
    }

    public function validUrlArray()
    {
        return [
            'with default port' => [
                'ws://example.com/foo/bar?foo=bar',
                'ws://example.com:80/foo/bar?foo=bar',
            ],
            'with user info' => [
                'wss://login:pass@example.com/',
                'wss://login:pass@example.com/',
            ],
        ];
    }

    /**
     * @dataProvider invalidArgumentExceptionProvider
     * @expectedException \InvalidArgumentException
     * @param $input
     */
    public function testConstructorThrowInvalidArgumentException($input)
    {
        Ws::createFromString($input);
    }

    public function invalidArgumentExceptionProvider()
    {
        return [
            ['ftp:example.com'],
            ['http://example.com'],
            [''],
            ['//example.com'],
        ];
    }

    /**
     * @dataProvider runtimeExceptionExceptionProvider
     * @expectedException \RuntimeException
     * @param $input
     */
    public function testConstructorThrowRuntimeException($input)
    {
        Ws::createFromString($input);
    }

    public function runtimeExceptionExceptionProvider()
    {
        return [
            ['wss:/example.com'],
            ['ws://example.com:80/foo/bar?foo=bar#content'],
        ];
    }
}

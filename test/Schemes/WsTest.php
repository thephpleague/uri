<?php

namespace League\Uri\Test\Schemes;

use League\Uri\Schemes\Ws;
use PHPUnit_Framework_TestCase;

/**
 * @group uri
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
        $this->assertSame($expected, Ws::createFromString($input)->__toString());
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
            ['wss:/example.com'],
            ['ws://example.com:80/foo/bar?foo=bar#content'],
        ];
    }

    public function testSetState()
    {
        $uri = Ws::createFromString('wss://a:b@c:442/d');
        $generateUri = eval('return '.var_export($uri, true).';');
        $this->assertEquals($uri, $generateUri);
    }
}

<?php

namespace League\Uri\test\Schemes;

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
            'empty URI' => [
                '',
                '',
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

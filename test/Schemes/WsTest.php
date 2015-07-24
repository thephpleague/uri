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
     * @expectedException InvalidArgumentException
     * @dataProvider isValidProvider
     */
    public function testIsValid($input)
    {
        Ws::createFromString($input);
    }

    public function isValidProvider()
    {
        return [
            ['ftp:example.com'],
            ['wss:/example.com'],
            ['http://example.com'],
            [''],
            ['ws://example.com:80/foo/bar?foo=bar#content'],
        ];
    }
}

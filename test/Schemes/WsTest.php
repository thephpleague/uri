<?php

namespace League\Uri\Test\Schemes;

use InvalidArgumentException;
use League\Uri\Schemes\Ws as WsUri;
use League\Uri\Test\AbstractTestCase;

/**
 * @group ws
 */
class WsTest extends AbstractTestCase
{
    /**
     * @dataProvider validUrlArray
     * @param $expected
     * @param $input
     */
    public function testCreateFromString($input, $expected)
    {
        $this->assertSame($expected, WsUri::createFromString($input)->__toString());
    }

    public function validUrlArray()
    {
        return [
            'with default port' => [
                'Ws://ExAmpLe.CoM:80/foo/bar?foo=bar',
                'ws://example.com/foo/bar?foo=bar',
            ],
            'with user info' => [
                'wss://login:pass@example.com/',
                'wss://login:pass@example.com/',
            ],
            'network path' => [
                '//ExAmpLe.CoM:21',
                '//example.com:21',
            ],
            'absolute path' => [
                '/path/to/my/file',
                '/path/to/my/file',
            ],
            'relative path' => [
                '.././path/../is/./relative',
                '.././path/../is/./relative',
            ],
            'empty string' => [
                '',
                '',
            ],
        ];
    }

    /**
     * @dataProvider invalidArgumentExceptionProvider
     * @expectedException InvalidArgumentException
     * @param $input
     */
    public function testConstructorThrowInvalidArgumentException($input)
    {
        WsUri::createFromString($input);
    }

    public function invalidArgumentExceptionProvider()
    {
        return [
            ['ftp:example.com'],
            ['http://example.com'],
            ['wss:/example.com'],
            ['//example.com:80/foo/bar?foo=bar#content'],
        ];
    }

    public function testSetState()
    {
        $uri = WsUri::createFromString('wss://a:b@c:442/d');
        $generateUri = eval('return '.var_export($uri, true).';');
        $this->assertEquals($uri, $generateUri);
    }
}

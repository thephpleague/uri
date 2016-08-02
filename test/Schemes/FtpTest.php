<?php

namespace League\Uri\Test\Schemes;

use InvalidArgumentException;
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Test\AbstractTestCase;

/**
 * @group ftp
 */
class FtpTest extends AbstractTestCase
{
    /**
     * @dataProvider validArray
     * @param $expected
     * @param $input
     */
    public function testCreateFromString($input, $expected)
    {
        $this->assertSame($expected, FtpUri::createFromString($input)->__toString());
    }

    public function validArray()
    {
        return [
            'with default port' => [
                'FtP://ExAmpLe.CoM:21/foo/bar',
                'ftp://example.com/foo/bar',
            ],
            'with user info' => [
                'ftp://login:pass@example.com/',
                'ftp://login:pass@example.com/',
            ],
            'with network path' => [
                '//ExAmpLe.CoM:80',
                '//example.com:80',
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
        FtpUri::createFromString($input);
    }

    public function invalidArgumentExceptionProvider()
    {
        return [
            ['wss:/example.com'],
            ['http://example.com'],
            ['ftp:example.com'],
            ['ftp://example.com?query#fragment'],
        ];
    }

    public function testSetState()
    {
        $uri = FtpUri::createFromString('ftp://a:b@c:442/d');
        $generateUri = eval('return '.var_export($uri, true).';');
        $this->assertEquals($uri, $generateUri);
    }
}

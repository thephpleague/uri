<?php

namespace League\Uri\Test\Schemes;

use InvalidArgumentException;
use League\Uri\Schemes\Ftp as FtpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group ftp
 */
class FtpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validArray
     * @param $expected
     * @param $input
     */
    public function testCreateFromString($expected, $input)
    {
        $this->assertSame($expected, FtpUri::createFromString($input)->__toString());
    }

    public function validArray()
    {
        return [
            'with default port' => [
                'ftp://example.com/foo/bar',
                'ftp://example.com:21/foo/bar',
            ],
            'with user info' => [
                'ftp://login:pass@example.com/',
                'ftp://login:pass@example.com/',
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
        FtpUri::createFromString($input);
    }

    public function runtimeExceptionExceptionProvider()
    {
        return [
            ['ftp:example.com'],
            ['ftp://example.com?query#fragment'],
        ];
    }
}

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
            'empty URI' => [
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

    /**
     * @dataProvider typecodeProvider
     * @param $input
     * @param $expected
     */
    public function testGetTypecode($input, $expected)
    {
        $this->assertSame($expected, FtpUri::createFromString($input)->getTypecode());
    }

    public function typecodeProvider()
    {
        return [
            'empty typecode' => ['ftp://example.com/foo/bar', FtpUri::TYPE_NONE],
            'empty typecode with directory' => ['ftp://example.com/foo/', FtpUri::TYPE_NONE],
            'typecode a' => ['ftp://example.com/foo/bar;type=a', FtpUri::TYPE_ASCII],
            'typecode i' => ['ftp://example.com/foo/bar;type=i', FtpUri::TYPE_BINARY],
            'typecode d' => ['ftp://example.com/foo/bar;type=d', FtpUri::TYPE_DIRECTORY],
            'typecode is case sensitive' => ['ftp://example.com/foo/bar;type=A', FtpUri::TYPE_NONE],
        ];
    }

    /**
     * @dataProvider typecodeModifierProvider
     * @param $input
     * @param $typecode
     * @param $expected
     */
    public function testWithTypecode($input, $typecode, $expected)
    {
        $this->assertSame($expected, (string) FtpUri::createFromString($input)->withTypecode($typecode));
    }

    public function typecodeModifierProvider()
    {
        return [
            'no modification (1)' => ['ftp://example.com/foo/bar', FtpUri::TYPE_NONE, 'ftp://example.com/foo/bar'],
            'no modification (2)' => ['ftp://example.com/foo;type=a/bar', FtpUri::TYPE_DIRECTORY, 'ftp://example.com/foo;type=a/bar;type=d'],
            'adding' => ['ftp://example.com/foo/bar', FtpUri::TYPE_ASCII, 'ftp://example.com/foo/bar;type=a'],
            'adding to empty path' => ['ftp://example.com', FtpUri::TYPE_DIRECTORY, 'ftp://example.com/;type=d'],
            'replacing' => ['ftp://example.com/foo/bar;type=i', FtpUri::TYPE_ASCII, 'ftp://example.com/foo/bar;type=a'],
            'removing' => ['ftp://example.com/foo/bar;type=d', FtpUri::TYPE_NONE, 'ftp://example.com/foo/bar'],
            'unable to typecode' => ['ftp://example.com/foo/bar;type=A', FtpUri::TYPE_NONE, 'ftp://example.com/foo/bar;type=A'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithTypecodeFailsWithInvalidTypecode()
    {
        FtpUri::createFromString('ftp://example.com/foo/bar')->withTypecode('Z');
    }

    /**
     * @dataProvider withExtensionProvider
     */
    public function testWithExtensionPreserveTypeCode($uri, $extension, $expected)
    {
        $this->assertSame(
            $expected,
            (string) FtpUri::createFromString($uri)->withExtension($extension)
        );
    }

    public function withExtensionProvider()
    {
        return [
            'no typecode' => ['ftp://example.com/foo/bar.csv', 'txt', 'ftp://example.com/foo/bar.txt'],
            'with typecode' => ['ftp://example.com/foo/bar.csv;type=a', 'txt', 'ftp://example.com/foo/bar.txt;type=a'],
            'remove extension with no typecode' => ['ftp://example.com/foo/bar.csv', '', 'ftp://example.com/foo/bar'],
            'remove extension with typecode' => ['ftp://example.com/foo/bar.csv;type=a', '', 'ftp://example.com/foo/bar;type=a'],
        ];
    }

    /**
     * @dataProvider getExtensionProvider
     *
     * @param $uri
     * @param $extension
     */
    public function testGetExtensionPreserveTypeCode($uri, $extension)
    {
        $ftp = FtpUri::createFromString($uri);
        $this->assertSame($extension, $ftp->path->getExtension());
    }

    public function getExtensionProvider()
    {
        return [
            'no typecode' => ['ftp://example.com/foo/bar.csv', 'csv'],
            'with typecode' => ['ftp://example.com/foo/bar.csv;type=a', 'csv'],
        ];
    }
}

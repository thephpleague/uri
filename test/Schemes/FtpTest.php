<?php

namespace League\Uri\test\Schemes;

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
     * @dataProvider isValidProvider
     * @expectedException InvalidArgumentException
     * @param $input
     */
    public function testIsValid($input)
    {
        FtpUri::createFromString($input);
    }

    public function isValidProvider()
    {
        return [
            ['ftp:example.com'],
            ['wss:/example.com'],
            ['http://example.com'],
            ['ftp://example.com:80/foo/bar?foo=bar#content'],
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
            'empty typecode' => ['ftp://example.com/foo/bar', ''],
            'empty typecode with directory' => ['ftp://example.com/foo/', ''],
            'typecode a' => ['ftp://example.com/foo/bar;type=a', 'a'],
            'typecode i' => ['ftp://example.com/foo/bar;type=i', 'i'],
            'typecode d' => ['ftp://example.com/foo/bar;type=d', 'd'],
            'typecode is case sensitive' => ['ftp://example.com/foo/bar;type=A', ''],
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
            'no modification (1)' => ['ftp://example.com/foo/bar', '', 'ftp://example.com/foo/bar'],
            'no modification (2)' => ['ftp://example.com/foo;type=a/bar', 'd', 'ftp://example.com/foo;type=a/bar;type=d'],
            'adding' => ['ftp://example.com/foo/bar', 'a', 'ftp://example.com/foo/bar;type=a'],
            'adding to empty path' => ['ftp://example.com', 'd', 'ftp://example.com/;type=d'],
            'replacing' => ['ftp://example.com/foo/bar;type=i', 'a', 'ftp://example.com/foo/bar;type=a'],
            'removing' => ['ftp://example.com/foo/bar;type=d', '', 'ftp://example.com/foo/bar'],
            'unable to typecode' => ['ftp://example.com/foo/bar;type=A', '', 'ftp://example.com/foo/bar;type=A'],
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
    public function testGetExtensionPreserveTypeCode($uri, $uri_extension, $path_extension)
    {
        $ftp = FtpUri::createFromString($uri);
        $this->assertSame($uri_extension, $ftp->getExtension());
        $this->assertSame($path_extension, $ftp->path->getExtension());
    }

    public function getExtensionProvider()
    {
        return [
            'no typecode' => ['ftp://example.com/foo/bar.csv', 'csv', 'csv'],
            'with typecode' => ['ftp://example.com/foo/bar.csv;type=a', 'csv', 'csv;type=a'],
        ];
    }
}

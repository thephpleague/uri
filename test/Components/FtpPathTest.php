<?php

namespace League\Uri\Test\Components;

use InvalidArgumentException;
use League\Uri\Components\FtpPath as Path;
use PHPUnit_Framework_TestCase;

/**
 * @group ftp
 */
class FtpPathTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider typecodeProvider
     * @param $input
     * @param $expected
     */
    public function testGetTypecode($input, $expected)
    {
        $this->assertSame($expected, (new Path($input))->getTypecode());
    }

    public function typecodeProvider()
    {
        return [
            'empty typecode' => ['/foo/bar', Path::TYPE_EMPTY],
            'empty typecode with directory' => ['/foo/', Path::TYPE_EMPTY],
            'typecode a' => ['foo/bar;type=a', Path::TYPE_ASCII],
            'typecode i' => ['/foo/bar;type=i', Path::TYPE_BINARY],
            'typecode d' => ['/foo/bar;type=d', Path::TYPE_DIRECTORY],
            'typecode is case sensitive' => ['/foo/bar;type=A', Path::TYPE_EMPTY],
        ];
    }

    /**
     * @dataProvider typecodeModifierProvider
     *
     * @param $input
     * @param $typecode
     * @param $expected
     */
    public function testWithTypecode($input, $typecode, $expected)
    {
        $this->assertSame($expected, (string) (new Path($input))->withTypecode($typecode));
    }

    public function typecodeModifierProvider()
    {
        return [
            'no modification (1)' => ['/foo/bar', Path::TYPE_EMPTY, '/foo/bar'],
            'no modification (2)' => ['/foo;type=a/bar', Path::TYPE_DIRECTORY, '/foo;type=a/bar;type=d'],
            'adding' => ['/foo/bar', Path::TYPE_ASCII, '/foo/bar;type=a'],
            'adding to empty path' => ['/', Path::TYPE_DIRECTORY, '/;type=d'],
            'replacing' => ['/foo/bar;type=i', Path::TYPE_ASCII, '/foo/bar;type=a'],
            'removing' => ['/foo/bar;type=d', Path::TYPE_EMPTY, '/foo/bar'],
            'unable to typecode' => ['/foo/bar;type=A', Path::TYPE_EMPTY, '/foo/bar;type=A'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithTypecodeFailsWithInvalidTypecode()
    {
        (new Path('ftp://example.com/foo/bar'))->withTypecode('Z');
    }

    /**
     * @dataProvider withExtensionProvider
     */
    public function testWithExtensionPreserveTypeCode($uri, $extension, $expected)
    {
        $this->assertSame(
            $expected,
            (string) (new Path($uri))->withExtension($extension)
        );
    }

    public function withExtensionProvider()
    {
        return [
            'no typecode' => ['/foo/bar.csv', 'txt', '/foo/bar.txt'],
            'with typecode' => ['/foo/bar.csv;type=a', 'txt', '/foo/bar.txt;type=a'],
            'remove extension with no typecode' => ['/foo/bar.csv', '', '/foo/bar'],
            'remove extension with typecode' => ['/foo/bar.csv;type=a', '', '/foo/bar;type=a'],
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
        $ftp = new Path($uri);
        $this->assertSame($extension, $ftp->getExtension());
    }

    public function getExtensionProvider()
    {
        return [
            'no typecode' => ['/foo/bar.csv', 'csv'],
            'with typecode' => ['/foo/bar.csv;type=a', 'csv'],
        ];
    }
}

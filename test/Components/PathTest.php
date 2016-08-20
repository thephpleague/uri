<?php

namespace League\Uri\Test\Components;

use InvalidArgumentException;
use League\Uri\Components\Path;
use League\Uri\Test\AbstractTestCase;

/**
 * @group path
 * @group segmentmodifier
 */
class PathTest extends AbstractTestCase
{
    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $component = new Path('yolo');
        $this->assertInternalType('array', $component->__debugInfo());
        ob_start();
        var_dump($component);
        $res = ob_get_clean();
        $this->assertContains($component->__toString(), $res);
        $this->assertContains('path', $res);
    }

    /**
     * @dataProvider validPathEncoding
     *
     * @param string $raw
     * @param string $parsed
     */
    public function testGetUriComponent($raw, $parsed)
    {
        $path = new Path($raw);
        $this->assertSame($parsed, $path->getUriComponent());
    }

    public function validPathEncoding()
    {
        return [
            ['toto', 'toto'],
            ['bar---', 'bar---'],
            ['', ''],
            ['"bad"', '%22bad%22'],
            ['<not good>', '%3Cnot%20good%3E'],
            ['{broken}', '%7Bbroken%7D'],
            ['`oops`', '%60oops%60'],
            ['\\slashy', '%5Cslashy'],
            ['foo^bar', 'foo%5Ebar'],
            ['foo^bar/baz', 'foo%5Ebar/baz'],
            ['to?to', 'to%3Fto'],
            ['to#to', 'to%23to'],
        ];
    }

    /**
     * @param $raw
     * @dataProvider invalidDataProvider
     * @expectedException InvalidArgumentException
     */
    public function testFailedConstructor($raw)
    {
        new Path($raw);
    }

    public function invalidDataProvider()
    {
        return [
            'bool' => [true],
            'Std Class' => [(object) 'foo'],
            'float' => [1.2],
            'array' => [['foo']],
        ];
    }

    /**
     * Test Removing Dot Segment
     *
     * @param $expected
     * @param $path
     * @dataProvider normalizeProvider
     */
    public function testWithoutDotSegments($path, $expected)
    {
        $this->assertSame($expected, (new Path($path))->withoutDotSegments()->__toString());
    }

    /**
     * Provides different segment to be normalized
     *
     * @return array
     */
    public function normalizeProvider()
    {
        return [
            ['/a/b/c/./../../g', '/a/g'],
            ['mid/content=5/../6', 'mid/6'],
            ['a/b/c', 'a/b/c'],
            ['a/b/c/.', 'a/b/c/'],
            ['/a/b/c', '/a/b/c'],
        ];
    }

    /**
     * @param $path
     * @param $expected
     * @dataProvider withoutEmptySegmentsProvider
     */
    public function testWithoutEmptySegments($path, $expected)
    {
        $this->assertSame($expected, (new Path($path))->withoutEmptySegments()->__toString());
    }

    public function withoutEmptySegmentsProvider()
    {
        return [
            ['/a/b/c', '/a/b/c'],
            ['//a//b//c', '/a/b/c'],
            ['a//b/c//', 'a/b/c/'],
            ['/a/b/c//', '/a/b/c/'],
        ];
    }

    /**
     * @param $path
     * @param $expected
     * @dataProvider trailingSlashProvider
     */
    public function testHasTrailingSlash($path, $expected)
    {
        $this->assertSame($expected, (new Path($path))->hasTrailingSlash());
    }

    public function trailingSlashProvider()
    {
        return [
            ['/path/to/my/', true],
            ['/path/to/my', false],
            ['path/to/my', false],
            ['path/to/my/', true],
            ['/', true],
            ['', false],
        ];
    }

    /**
     * @param $path
     * @param $expected
     * @dataProvider withTrailingSlashProvider
     */
    public function testWithTrailingSlash($path, $expected)
    {
        $this->assertSame($expected, (string) (new Path($path))->withTrailingSlash());
    }

    public function withTrailingSlashProvider()
    {
        return [
            'relative path without ending slash' => ['toto', 'toto/'],
            'absolute path without ending slash' => ['/toto', '/toto/'],
            'root path' => ['/', '/'],
            'empty path' => ['', '/'],
            'relative path with ending slash' => ['toto/', 'toto/'],
            'absolute path with ending slash' => ['/toto/', '/toto/'],
        ];
    }

    /**
     * @param $path
     * @param $expected
     * @dataProvider withoutTrailingSlashProvider
     */
    public function testWithoutTrailingSlash($path, $expected)
    {
        $this->assertSame($expected, (string) (new Path($path))->withoutTrailingSlash());
    }

    public function withoutTrailingSlashProvider()
    {
        return [
            'relative path without ending slash' => ['toto', 'toto'],
            'absolute path without ending slash' => ['/toto', '/toto'],
            'root path' => ['/', ''],
            'empty path' => ['', ''],
            'relative path with ending slash' => ['toto/', 'toto'],
            'absolute path with ending slash' => ['/toto/', '/toto'],
        ];
    }

    /**
     * @param $path
     * @param $expected
     * @dataProvider withLeadingSlashProvider
     */
    public function testWithLeadingSlash($path, $expected)
    {
        $this->assertSame($expected, (string) (new Path($path))->withLeadingSlash());
    }

    public function withLeadingSlashProvider()
    {
        return [
            'relative path without leading slash' => ['toto', '/toto'],
            'absolute path' => ['/toto', '/toto'],
            'root path' => ['/', '/'],
            'empty path' => ['', '/'],
            'relative path with ending slash' => ['toto/', '/toto/'],
            'absolute path with ending slash' => ['/toto/', '/toto/'],
        ];
    }

    /**
     * @param $path
     * @param $expected
     * @dataProvider withoutLeadingSlashProvider
     */
    public function testWithoutLeadingSlash($path, $expected)
    {
        $this->assertSame($expected, (string) (new Path($path))->withoutLeadingSlash());
    }

    public function withoutLeadingSlashProvider()
    {
        return [
            'relative path without ending slash' => ['toto', 'toto'],
            'absolute path without ending slash' => ['/toto', 'toto'],
            'root path' => ['/', ''],
            'empty path' => ['', ''],
            'absolute path with ending slash' => ['/toto/', 'toto/'],
        ];
    }

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
            'empty typecode' => ['/foo/bar', Path::FTP_TYPE_EMPTY],
            'empty typecode with directory' => ['/foo/', Path::FTP_TYPE_EMPTY],
            'typecode a' => ['foo/bar;type=a', Path::FTP_TYPE_ASCII],
            'typecode i' => ['/foo/bar;type=i', Path::FTP_TYPE_BINARY],
            'typecode d' => ['/foo/bar;type=d', Path::FTP_TYPE_DIRECTORY],
            'typecode is case sensitive' => ['/foo/bar;type=A', Path::FTP_TYPE_EMPTY],
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
            'no modification (1)' => ['/foo/bar', Path::FTP_TYPE_EMPTY, '/foo/bar'],
            'no modification (2)' => ['/foo;type=a/bar', Path::FTP_TYPE_DIRECTORY, '/foo;type=a/bar;type=d'],
            'adding' => ['/foo/bar', Path::FTP_TYPE_ASCII, '/foo/bar;type=a'],
            'adding to empty path' => ['/', Path::FTP_TYPE_DIRECTORY, '/;type=d'],
            'replacing' => ['/foo/bar;type=i', Path::FTP_TYPE_ASCII, '/foo/bar;type=a'],
            'removing' => ['/foo/bar;type=d', Path::FTP_TYPE_EMPTY, '/foo/bar'],
            'unable to typecode' => ['/foo/bar;type=A', Path::FTP_TYPE_EMPTY, '/foo/bar;type=A'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithTypecodeFailsWithInvalidTypecode()
    {
        (new Path('ftp://example.com/foo/bar'))->withTypecode('Z');
    }
}

<?php

namespace League\Uri\test\Components;

use ArrayIterator;
use League\Uri\Components\HierarchicalPath as Path;
use PHPUnit_Framework_TestCase;

/**
 * @group path
 */
class HierarchicalPathTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $raw
     * @param string $parsed
     * @dataProvider validPathProvider
     */
    public function testValidPath($raw, $parsed)
    {
        $path = new Path($raw);
        $this->assertSame($parsed, $path->__toString());
    }

    public function validPathProvider()
    {
        return [
            ['', ''],
            ['/path/to/my/file.csv', '/path/to/my/file.csv'],
            ['you', 'you'],
            ['foo/bar/', 'foo/bar/'],
            ['', ''],
            ['/', '/'],
            ['/shop/rev iew/', '/shop/rev%20iew/'],
            ['/master/toto/a%c2%b1b', '/master/toto/a%C2%B1b'],
            ['/master/toto/%7Eetc', '/master/toto/~etc'],
            ['////master/toto/%7Eetc', '////master/toto/~etc'],
        ];
    }

    /**
     * @param $str
     * @expectedException InvalidArgumentException
     * @dataProvider failedConstructor
     */
    public function testInvalidPath($str)
    {
        new Path($str);
    }

    public function failedConstructor()
    {
        return [
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'null'      => [null],
            'float'     => [1.2],
        ];
    }

    /**
     * @param $raw
     * @param $expected
     * @dataProvider isAbsoluteProvider
     */
    public function testIsAbsolute($raw, $expected)
    {
        $path = new Path($raw);
        $this->assertSame($expected, $path->isAbsolute());
    }

    public function isAbsoluteProvider()
    {
        return [
            ['', false],
            ['/', true],
            ['../..', false],
            ['/a/b/c', true],
        ];
    }

    /**
     * @param string $raw
     * @param int    $key
     * @param string $value
     * @param mixed  $default
     * @dataProvider getSegmentProvider
     */
    public function testGetSegment($raw, $key, $value, $default)
    {
        $path = new Path($raw);
        $this->assertSame($value, $path->getSegment($key, $default));
    }

    public function getSegmentProvider()
    {
        return [
            ['/shop/rev iew/', 1, 'rev iew', null],
            ['/shop/rev%20iew/', 1, 'rev iew', null],
            ['/shop/rev%20iew/', 28, 'foo', 'foo'],
        ];
    }

    /**
     * @param $input
     * @param $has_front_delimiter
     * @param $expected
     * @dataProvider createFromArrayValid
     */
    public function testCreateFromArray($input, $has_front_delimiter, $expected)
    {
        $this->assertSame($expected, Path::createFromArray($input, $has_front_delimiter)->__toString());
    }

    public function createFromArrayValid()
    {
        return [
            'array' => [['www', 'example', 'com'], Path::IS_RELATIVE, 'www/example/com'],
            'array' => [['www', 'example', 'com'], Path::IS_ABSOLUTE, '/www/example/com'],
            'iterator' => [new ArrayIterator(['www', 'example', 'com']), Path::IS_ABSOLUTE, '/www/example/com'],
            'Path object' => [new Path('/foo/bar/baz'), Path::IS_ABSOLUTE, '/foo/bar/baz'],
            'arbitrary cut 1' => [['foo', 'bar', 'baz'], Path::IS_ABSOLUTE, '/foo/bar/baz'],
            'arbitrary cut 2' => [['foo/bar', 'baz'], Path::IS_ABSOLUTE, '/foo/bar/baz'],
            'arbitrary cut 3' => [['foo/bar/baz'], Path::IS_ABSOLUTE, '/foo/bar/baz'],
            'ending delimiter' => [['foo/bar/baz', ''], Path::IS_RELATIVE, 'foo/bar/baz/'],
        ];
    }

    /**
     * @param $input
     * @param $flags
     * @dataProvider createFromArrayInvalid
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromArrayFailed($input, $flags)
    {
        Path::createFromArray($input, $flags);
    }

    public function createFromArrayInvalid()
    {
        return [
            'string' => ['www.example.com', Path::IS_RELATIVE],
            'bool' => [true, Path::IS_RELATIVE],
            'integer' => [1, Path::IS_RELATIVE],
            'object' => [new \StdClass(), Path::IS_RELATIVE],
            'unknown flag' => [['all', 'is', 'good'], 23],
            'use reserved characters #' => [['all', 'i#s', 'good'], Path::IS_ABSOLUTE],
            'use reserved characters ?' => [['all', 'i?s', 'good'], Path::IS_RELATIVE],
        ];
    }

    /**
     * @param $source
     * @param $prepend
     * @param $res
     * @dataProvider prependData
     */
    public function testPrepend($source, $prepend, $res)
    {
        $path    = new Path($source);
        $newPath = $path->prepend($prepend);
        $this->assertSame($res, $newPath->__toString());
    }

    public function prependData()
    {
        return [
            ['/test/query.php', new Path('/master'), '/master/test/query.php'],
            ['/test/query.php', new Path('/master/'), '/master/test/query.php'],
            ['/test/query.php', new Path(''), '/test/query.php'],
            ['/test/query.php', new Path('/'), '/test/query.php'],
            ['/test/query.php', '/master', '/master/test/query.php'],
            ['/test/query.php', '/master/', '/master/test/query.php'],
            ['/test/query.php', '', '/test/query.php'],
            ['/test/query.php', '/', '/test/query.php'],
        ];
    }

    /**
     * @param $source
     * @param $append
     * @param $res
     * @dataProvider appendData
     */
    public function testAppend($source, $append, $res)
    {
        $path    = new Path($source);
        $newPath = $path->append($append);
        $this->assertSame($res, $newPath->__toString());
    }

    public function appendData()
    {
        return [
            ['/test/', new Path('/master/'), '/test/master/'],
            ['/test/', new Path('/master'),  '/test/master'],
            ['/test',  new Path('master'),   '/test/master'],
            ['test',   new Path('master'),   'test/master'],
            ['test',   new Path('/master'),  'test/master'],
            ['test',   new Path('master/'),  'test/master/'],
            ['/test/', '/master/', '/test/master/'],
            ['/test/', '/master',  '/test/master'],
            ['/test',  'master',   '/test/master'],
            ['test',   'master',   'test/master'],
            ['test',   '/master',  'test/master'],
            ['test',   'master/',  'test/master/'],
        ];
    }

    /**
     * Test AbstractSegment::without
     *
     * @param $origin
     * @param $without
     * @param $result
     *
     * @dataProvider withoutProvider
     */
    public function testWithout($origin, $without, $result)
    {
        $this->assertSame($result, (string) (new Path($origin))->without($without));
    }

    public function withoutProvider()
    {
        return [
            ['/test/query.php', [4], '/test/query.php'],
            ['/master/test/query.php', [2], '/master/test'],
            ['/toto/le/heros/masson', [0], '/le/heros/masson'],
            ['/toto/le/heros/masson', [2, 3], '/toto/le'],
            ['/toto/le/heros/masson', [1, 2], '/toto/masson'],
            ['/toto/le/heros/masson', function ($value) {
                return $value < 3;
            }, '/masson'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithoutFaild()
    {
        (new Path('/toofan/orobo'))->without('toofan');
    }

    /**
     * @param $raw
     * @param $input
     * @param $offset
     * @param $expected
     * @dataProvider replaceValid
     */
    public function testreplace($raw, $input, $offset, $expected)
    {
        $path = new Path($raw);
        $newPath = $path->replace($offset, $input);
        $this->assertSame($expected, $newPath->__toString());
    }

    public function replaceValid()
    {
        return [
            ['/path/to/the/sky', new Path('shop'), 0, '/shop/to/the/sky'],
            ['', new Path('shoki'), 0, 'shoki'],
            ['', new Path('shoki/'), 0, 'shoki'],
            ['', new Path('/shoki/'), 0, 'shoki'],
            ['/path/to/paradise', new Path('::1'), 42, '/path/to/paradise'],
            ['/path/to/the/sky', 'shop', 0, '/shop/to/the/sky'],
            ['', 'shoki', 0, 'shoki'],
            ['', 'shoki/', 0, 'shoki'],
            ['', '/shoki/', 0, 'shoki'],
            ['/path/to/paradise', '::1', 42, '/path/to/paradise'],
        ];
    }

    public function testKeys()
    {
        $path = new Path('/bar/3/troll/3');
        $this->assertCount(4, $path->keys());
        $this->assertCount(0, $path->keys('foo'));
        $this->assertSame([0], $path->keys('bar'));
        $this->assertCount(2, $path->keys('3'));
        $this->assertSame([1, 3], $path->keys('3'));
    }

    /**
     * @param $input
     * @param $toArray
     * @param $nbSegment
     * @dataProvider arrayProvider
     */
    public function testCountable($input, $toArray, $nbSegment)
    {
        $path = new Path($input);
        $this->assertCount($nbSegment, $path);
        $this->assertSame($toArray, $path->toArray());
    }

    public function arrayProvider()
    {
        return [
            ['/toto/le/heros/masson', ['toto', 'le', 'heros', 'masson'], 4],
            ['toto/le/heros/masson', ['toto', 'le', 'heros', 'masson'], 4],
            ['/toto/le/heros/masson/', ['toto', 'le', 'heros', 'masson', ''], 5],
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
     * @dataProvider relativizeProvider
     */
    public function testRelativize($base, $child, $expected)
    {
        $this->assertSame($expected, (string) (new Path($base))->relativize(new Path($child)));
    }

    public function relativizeProvider()
    {
        return [
            ['/toto/le/heros', '/bar', '../bar'],
            ['/toto/le/heros/', '/bar', '../bar'],
            ['toto/le/heros/', '/bar', '/bar'],
            ['toto/le/heros', '/bar', '/bar'],
            ['toto/le/heros/', 'bar', 'bar'],
            ['toto/le/heros', 'bar', 'bar'],
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

    public function testGetBasemane()
    {
        $path = new Path('/path/to/my/file.txt');
        $this->assertSame('file.txt', $path->getBasename());
    }

    /**
     * @param $path
     * @param $dirname
     * @dataProvider dirnameProvider
     */
    public function testGetDirmane($path, $dirname)
    {
        $this->assertSame($dirname, (new Path($path))->getDirname());
    }

    public function dirnameProvider()
    {
        return [
            ['/path/to/my/file.txt', '/path/to/my'],
            ['/path/to/my/file/', '/path/to/my'],
            ['/path/to/my\\file/', '/path/to'],
            ['.', '.'],
            ['/path/to/my//file/', '/path/to/my'],
            ['', ''],
            ['/', '/'],
            ['/path/to/my/../file.txt', '/path/to/my/..'],
        ];
    }

    public function testGetBasemaneWithEmptyBasename()
    {
        $path = new Path('/path/to/my/');
        $this->assertEmpty($path->getBasename());
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
            'root path'                          => ['/', '/'],
            'empty path'                         => ['', '/'],
            'relative path with ending slash'    => ['toto/', 'toto/'],
            'absolute path with ending slash'    => ['/toto/', '/toto/'],
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
            'root path'                          => ['/', ''],
            'empty path'                         => ['', ''],
            'relative path with ending slash'    => ['toto/', 'toto'],
            'absolute path with ending slash'    => ['/toto/', '/toto'],
        ];
    }

    /**
     * @param $raw
     * @param $parsed
     * @dataProvider extensionProvider
     */
    public function testGetExtension($raw, $parsed)
    {
        $this->assertSame($parsed, (new Path($raw))->getExtension());
    }

    public function extensionProvider()
    {
        return [
            ['/path/to/my/', ''],
            ['/path/to/my/file', ''],
            ['/path/to/my/file.txt', 'txt'],
            ['/path/to/my/file.csv.txt', 'txt'],
        ];
    }

    /**
     * @param $raw
     * @param $raw_ext
     * @param $new_path
     * @param $parsed_ext
     * @dataProvider withExtensionProvider
     */
    public function testWithExtension($raw, $raw_ext, $new_path, $parsed_ext)
    {
        $newPath = (new Path($raw))->withExtension($raw_ext);
        $this->assertSame($new_path, (string) $newPath);
        $this->assertSame($parsed_ext, $newPath->getExtension());
    }

    public function withExtensionProvider()
    {
        return [
            ['/path/to/my/file.txt', '.csv', '/path/to/my/file.csv', 'csv'],
            ['/path/to/my/file.txt', 'csv', '/path/to/my/file.csv', 'csv'],
            ['/path/to/my/file', '.csv', '/path/to/my/file.csv', 'csv'],
            ['/path/to/my/file.csv', '.csv', '/path/to/my/file.csv', 'csv'],
            ['/path/to/my/file.csv', '', '/path/to/my/file', ''],
            ['/path/to/my/file.tar.gz', 'bz2', '/path/to/my/file.tar.bz2', 'bz2'],
            ['', 'csv', '', ''],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithExtensionWithInvalidExtension()
    {
        (new Path())->withExtension('t/xt');
    }

    /**
     * @param $params
     * @param $callable
     * @param $expected
     * @dataProvider filterProvider
     */
    public function testFilter($params, $callable, $expected)
    {
        $obj = Path::createFromArray($params, Path::IS_ABSOLUTE)->filter($callable, Path::FILTER_USE_VALUE);
        $this->assertSame($expected, $obj->__toString());
    }

    public function filterProvider()
    {
        $func = function ($value) {
            return stripos($value, '.') !== false;
        };

        return [
            'empty query'  => [[], $func, '/'],
            'remove One'   => [['toto', 'foo.bar', 'st.ay'], $func, '/foo.bar/st.ay'],
            'remove All'   => [['foobar', 'stay'], $func, '/'],
            'remove None'  => [['foo.bar', 'st.ay'], $func, '/foo.bar/st.ay'],
        ];
    }

    public function pathTestProvider()
    {
        return [
            // Percent encode spaces.
            ['/baz bar', '/baz%20bar'],
            // Don't encoding something that's already encoded.
            ['/baz%20bar', '/baz%20bar'],
            // Percent encode invalid percent encodings
            ['/baz%2-bar', '/baz%252-bar'],
            // Don't encode path segments
            ['/baz/bar/bam~a', '/baz/bar/bam~a'],
            ['/baz+bar', '/baz+bar'],
            ['/baz:bar', '/baz:bar'],
            ['/baz@bar', '/baz@bar'],
            ['/baz(bar);bam/', '/baz(bar);bam/'],
            ['/a-zA-Z0-9.-_~!$&\'()*+,;=:@', '/a-zA-Z0-9.-_~!$&\'()*+,;=:@'],
        ];
    }
    /**
     * @dataProvider pathTestProvider
     */
    public function testUriEncodesPathProperly($input, $output)
    {
        $this->assertSame($output, (new Path($input))->__toString());
    }
}

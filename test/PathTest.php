<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Path;
use PHPUnit_Framework_TestCase;
use StdClass;

/**
 * @group segment
 */
class PathTest extends PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $expected = '/path/to/mars';
        $path = new Path($expected);
        $this->assertSame($expected, $path->__toString());
    }

    public function testEmptyConstructor()
    {
        $path = new Path();
        $this->assertSame('/', (string) $path);
    }

    public function testPrepend()
    {
        $path = new Path('/test/query.php');
        $newPath = $path->prependWith('master');
        $this->assertSame('master/test/query.php', $newPath->get());
    }

    public function testNormalizePath()
    {
        $path = new Path('/shop/rev iew/');
        $this->assertSame('shop/rev%20iew/', $path->get());
    }

    public function testGetValueEncoded()
    {
        $path = new Path('/shop/rev iew/');
        $this->assertSame('rev iew', $path->getValue(1));
    }

    public function testMultiplePrepend()
    {
        $path = new Path('/master/query.php');
        $newPath = $path
            ->prependWith('master')
            ->prependWith('master');
        $this->assertSame('/master/master/master/query.php', (string) $newPath);
    }

    public function testAppendEmptyPath()
    {
        $expected = '/shop/checkout/';
        $this->assertSame($expected, (string) (new Path())->appendWith($expected));
    }

    /**
     * Test AbstractSegment::without
     *
     * @param string $origin
     * @param string $without
     * @param string $result
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
            ['/test/query.php', 'toto', '/test/query.php'],
            ['/test/query.php', '  ', '/test/query.php'],
            ['/master/test/query.php', 'query.php', '/master/test/'],
            ['/toto/le/heros/masson', 'toto', '/le/heros/masson'],
            ['/toto/le/heros/masson', 'ros/masson', '/toto/le/heros/masson'],
            ['/toto/le/heros/masson', 'asson', '/toto/le/heros/masson'],
            ['/toto/le/heros/masson', '/heros/masson', '/toto/le'],
            ['/toto/le/heros/masson', '/le/heros', '/toto/masson'],
        ];
    }

    public function testKeys()
    {
        $path = new Path('/bar/3/troll/3');
        $this->assertCount(4, $path->getKeys());
        $this->assertCount(0, $path->getKeys('foo'));
        $this->assertSame([0], $path->getKeys('bar'));
        $this->assertCount(2, $path->getKeys('3'));
        $this->assertSame([1, 3], $path->getKeys('3'));
    }

    public function testGetValue()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertSame('toto', $path->getValue(0));
        $this->assertNull($path->getValue(23));
        $this->assertSame('foo', $path->getValue(23, 'foo'));
    }

    public function testCountable()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertCount(4, $path);
    }

    public function testSegmentNormalization()
    {
        $path = new Path('/master/toto/a%c2%b1b');
        $this->assertSame('/master/toto/a%C2%B1b', (string) $path);

        $path = new Path('/master/toto/%7Eetc');
        $this->assertSame('/master/toto/~etc', (string) $path);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadPath()
    {
        new Path(new StdClass());
    }

    /**
     * Test Removing Dot Segment
     *
     * @param  string $expected
     * @param  string $path
     * @dataProvider normalizeProvider
     */
    public function testNormalize($expected, $path)
    {
        $path = new Path($path);
        $this->assertSame($expected, $path->normalize()->__toString());
    }

    /**
     * Provides different segment to be normalized
     *
     * @return array
     */
    public function normalizeProvider()
    {
        return [
            ['/path/to/mars', '/path/to/mars'],
            ['/a/b/c/%7Bfoo%7D', '/../a/./b/../b/%63/%7bfoo%7d'],
            ['/bar', '../bar'],
            ['/bar', './bar'],
            ['/bar', '.././bar'],
            ['/bar', '.././bar'],
            ['/foo/bar', '/foo/./bar'],
            ['/bar/', '/bar/./'],
            ['/', '/.'],
            ['/bar/', '/bar/.'],
            ['/bar', '/foo/../bar'],
            ['/', '/bar/../'],
            ['/', '/..'],
            ['/', '/bar/..'],
            ['/foo/', '/foo/bar/..'],
            ['/', '.'],
            ['/', '..'],
            ['/', '../aaa/./..'],
        ];
    }
}

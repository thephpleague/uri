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
        $path    = new Path();
        $newPath = $path->appendWith('/shop/checkout/');
        $this->assertSame('shop/checkout/', $newPath->get());
    }

    public function testRemoveUnknownSegment()
    {
        $path    = new Path('/test/query.php');
        $newPath = $path->without('toto');
        $this->assertEquals($path, $newPath);
    }

    public function testRemoveEmptyString()
    {
        $path    = new Path('/test/query.php');
        $newPath = $path->without('');
        $this->assertEquals($path, $newPath);
    }

    public function testRemoveLastSegment()
    {
        $path = new Path('/master/test/query.php');
        $newPath = $path->without('query.php');
        $this->assertSame('master/test/', $newPath->get());
    }

    public function testRemoveFirstSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $newPath = $path->without('toto');
        $this->assertSame('/le/heros/masson', (string) $newPath);
    }

    public function testRemoveTruncatedSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $newPath = $path->without('ros/masson');
        $this->assertSame('/toto/le/heros/masson', (string) $newPath);
    }

    public function testRemoveUncompleteSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $newPath = $path->without('asson');
        $this->assertSame('/toto/le/heros/masson', (string) $newPath);
    }

    public function testRemoveMultipleSegment()
    {
        $path    = new Path('/toto/le/heros/masson');
        $newPath = $path->without('/heros/masson');
        $this->assertSame('/toto/le', (string) $newPath);
    }

    public function testRemoveContainedSegment()
    {
        $path    = new Path('/toto/le/heros/masson');
        $newPath = $path->without('/le/heros');
        $this->assertSame('/toto/masson', (string) $newPath);
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

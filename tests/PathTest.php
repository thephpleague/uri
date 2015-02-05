<?php

namespace League\Url\Test\Components;

use ArrayIterator;
use League\Url\Path;
use PHPUnit_Framework_TestCase;
use StdClass;

/**
 * @group components
 */
class PathTest extends PHPUnit_Framework_TestCase
{
    public function testPrepend()
    {
        $path = new Path('/test/query.php');
        $path->prepend('master');
        $this->assertSame('master/test/query.php', $path->get());
    }

    public function testAppendWithWhence()
    {
        $path = new Path('/master/query.php');
        $path->append('sullivent', 'master');
        $this->assertSame('master/sullivent/query.php', $path->get());
    }

    public function testSet()
    {
        $path = new Path('/master/query.php');
        $path->set(null);
        $this->assertSame('', (string) $path);
    }

    public function testSetWithArray()
    {
        $path = new Path();
        $path->set(['shop', 'rev iew', '']);
        $this->assertSame('shop/rev%20iew/', $path->get());
    }

    public function testSetWithTraversable()
    {
        $path = new Path();
        $path->set(new ArrayIterator(['sullivent', 'wacowski', '']));
        $this->assertSame('sullivent/wacowski/', $path->get());
    }

    public function testMultiplePrepend()
    {
        $path = new Path('/master/query.php');
        $path->prepend('master');
        $path->prepend('master');
        $this->assertSame('master/master/master/query.php', (string) $path);
    }

    public function testMultipleAppendWithWhence()
    {
        $path = new Path('/master/query.php');
        $path->append('slave', 'master');
        $path->append('slave', 'master');
        $this->assertSame('master/slave/slave/query.php', (string) $path);
    }

    public function testAppendEmptyPath()
    {
        $path = new Path();
        $path->append('/shop/checkout/');
        $this->assertSame('shop/checkout/', $path->get());
    }

    public function testRemoveUnknownSegment()
    {
        $path = new Path('/test/query.php');
        $this->assertFalse($path->remove('toto'));
        $this->assertSame('test/query.php', $path->get());
    }

    public function testRemoveEmptyString()
    {
        $path = new Path('/test/query.php');
        $this->assertTrue($path->remove(''));
        $this->assertSame('test/query.php', $path->get());
    }

    public function testRemoveLastSegment()
    {
        $path = new Path('/master/test/query.php');
        $this->assertTrue($path->remove('query.php'));
        $this->assertSame('master/test', $path->get());
    }

    public function testRemoveFirstSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertTrue($path->remove('toto'));
        $this->assertSame('le/heros/masson', (string) $path);
    }

    public function testRemoveTruncatedSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertFalse($path->remove('ros/masson'));
        $this->assertSame('toto/le/heros/masson', (string) $path);
    }

    public function testRemoveUncompleteSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertFalse($path->remove('asson'));
        $this->assertSame('toto/le/heros/masson', (string) $path);
    }

    public function testRemoveMultipleSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertTrue($path->remove('/heros/masson'));
        $this->assertSame('toto/le', (string) $path);
    }

    public function testRemoveContainedSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertTrue($path->remove('/le/heros'));
        $this->assertSame('toto/masson', (string) $path);
    }

    public function testKeys()
    {
        $path = new Path(['bar', 3, 'troll', 3]);
        $this->assertCount(4, $path->keys());
        $this->assertCount(0, $path->keys('foo'));
        $this->assertSame([0], $path->keys('bar'));
        $this->assertCount(2, $path->keys('3'));
        $this->assertSame([1, 3], $path->keys('3'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadPath()
    {
        new Path(new StdClass());
    }

    public function testPrependWithWhenceIndex()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->prepend('foo', 'toto', 1);
        $this->assertSame('/toto/foo/toto/shoky/master', $path->getUriComponent());
    }

    /**
     * Test Removing Dot Segment
     *
     * @param  string $expected [description]
     * @param  string $path     [description]
     * @dataProvider normalizeProvider
     */
    public function testNormalize($expected, $path)
    {
        $path = new Path($path);
        $newPath = $path->normalize();
        $this->assertInstanceOf('League\Url\Interfaces\PathInterface', $newPath);
        $this->assertSame($expected, $newPath->getUriComponent());
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

    public function testGetSegment()
    {
        $path = new Path('/toto/le/heros/masson');
        $this->assertSame('toto', $path->getSegment(0));
        $this->assertNull($path->getSegment(23));
        $this->assertSame('foo', $path->getSegment(23, 'foo'));
    }

    public function testSetSegment()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->setSegment(0, 'slave');
        $this->assertSame('slave', $path->getSegment(0));
        $this->assertSame('slave/toto/shoky/master', (string) $path);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testSetSegmentInvalidOffset()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->setSegment(23, 'foo');
    }

    public function testSegmentNormalization()
    {
        $path = new Path('/master/toto/a%c2%b1b');
        $this->assertSame('master/toto/a%C2%B1b', (string) $path);

        $path = new Path('/master/toto/%7Eetc');
        $this->assertSame('master/toto/~etc', (string) $path);
    }

    public function testSetSegmentRemoveOffsetWithNullAndEmptyValue()
    {
        $path = new Path('/toto/toto/shoky/master');
        $path->setSegment(0, null);
        $this->assertSame('toto/shoky/master', (string) $path);
        $path->setSegment(0, '');
        $this->assertSame('/shoky/master', $path->getUriComponent());
    }
}

<?php

namespace Bakame\Url\Components;

class PathTest extends \PHPUnit_Framework_TestCase
{

    private $segment;

    public function setUp()
    {
        $path = '/foo/bar/baz';
        $separator = '/';
        $this->segment = new Path($path, $separator);
    }

    public function testCountable()
    {
        $this->assertCount(3, $this->segment);
    }

    public function testIterator()
    {
        foreach ($this->segment as $key => $value) {
            $this->assertSame($value, $this->segment->get($key));
        }
    }

    public function testConstructor()
    {
        $path = '/foo/bar/baz';
        $separator = '/';
        $segment = new Path($path, $separator);
        $res = $segment->all();
        $this->assertInternalType('array', $res);
        $this->assertCount(3, $res);
        $this->assertSame(array('foo', 'bar', 'baz'), $res);
    }

    public function testConstructor2()
    {
        $path = '/foo/bar/baz/';
        $separator = '/';
        $segment = new Path($path, $separator);
        $res = $segment->all();
        $this->assertInternalType('array', $res);
        $this->assertCount(3, $res);
        $this->assertSame(array('foo', 'bar', 'baz'), $res);
    }

    public function testHas()
    {
        $this->assertTrue($this->segment->has('foo'));
        $this->assertFalse($this->segment->has('toto'));
    }

    public function testGet()
    {
        $res = $this->segment->get(0);
        $this->assertSame('foo', $res);
        $this->assertNull($this->segment->get('foo'));
    }

    public function testClear()
    {
        $res = $this->segment
            ->clear()
            ->all();
        $this->assertInternalType('array', $res);
        $this->assertCount(0, $res);
    }

    public function testSet()
    {
        $this->segment->set('toto');
        $this->assertSame('foo/bar/baz/toto', $this->segment->__toString());

        $this->segment->set('toto', 'prepend');
        $this->assertSame('toto/foo/bar/baz/toto', $this->segment->__toString());

        $this->segment->set('toto', 'prepend', 'toto');
        $this->assertSame('toto/toto/foo/bar/baz/toto', $this->segment->__toString());

        $this->segment->set('toto', 'sdfqsdfsd', 'toto');
        $this->assertSame('toto/toto/foo/bar/baz/toto/toto', $this->segment->__toString());

        $this->segment->set('toto', 'sdfqsdfsd', 'toto', 3);
        $this->assertSame('toto/toto/foo/bar/baz/toto/toto/toto', $this->segment->__toString());

        $this->segment->clear();
        $this->assertSame('', $this->segment->__toString());

        $this->segment
            ->set(array('inscription', 'foo', 'bar'))
            ->set('john', 'prepend', 'foo', 8)
            ->set('jane', 'prepend', 'bar', 0);
        $this->assertSame('inscription/john/foo/jane/bar', $this->segment->__toString());

        $this->segment->remove('bar');
        $this->assertSame('inscription/john/foo/jane', $this->segment->__toString());
    }
}

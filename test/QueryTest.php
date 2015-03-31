<?php

namespace League\Url\Test;

use ArrayIterator;
use League\Url\Query;
use PHPUnit_Framework_TestCase;
use StdClass;

/**
 * @group query
 */
class QueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Query
     */
    protected $query;

    public function setUp()
    {
        $this->query = new Query('?kingkong=toto');
    }

    public function testGetUriComponent()
    {
        $this->assertSame('?kingkong=toto', $this->query->getUriComponent());
    }

    public function testGetUriComponentWithoutArgument()
    {
        $query = new Query();
        $this->assertSame('', $query->getUriComponent());
    }

    public function testCreateFromArrayWithTraversable()
    {
        $query = Query::createFromArray(new ArrayIterator(['john' => 'doe the john']));
        $this->assertCount(1, $query);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFromArrayFailed()
    {
        Query::createFromArray(new \StdClass);
    }

    public function testSameValueAs()
    {
        $empty_query = new Query();
        $this->assertFalse($empty_query->sameValueAs($this->query));
        $query = $empty_query->mergeWith($this->query);
        $this->assertTrue($query->sameValueAs($this->query));
    }

    public function testMergeWithWithArray()
    {
        $query = $this->query->mergeWith(['john' => 'doe the john']);
        $this->assertSame('kingkong=toto&john=doe%20the%20john', (string) $query);
    }

    public function testMergeWithWithArrayIterator()
    {
        $query = $this->query->mergeWith(new ArrayIterator(['john' => 'doe the john']));
        $this->assertSame('kingkong=toto&john=doe%20the%20john', (string) $query);
    }

    public function testMergeWithWithQueryInterface()
    {
        $query = $this->query->mergeWith(Query::createFromArray(['foo' => 'bar']));
        $this->assertSame('kingkong=toto&foo=bar', (string) $query);
    }

    public function testMergeWithWithString()
    {
        $query = $this->query->mergeWith('?kingkong=tata');
        $this->assertSame('kingkong=tata', (string) $query);
    }

    public function testMergeWithEmptyString()
    {
        $query = $this->query->mergeWith('');
        $this->assertSame('kingkong=toto', (string) $query);
    }

    public function testMergeWithRemoveArg()
    {
        $query = $this->query->mergeWith(['kingkong' => null]);
        $this->assertSame('', (string) $query);
    }

    public function testSetterWithNull()
    {
        $query = $this->query->mergeWith(null);
        $this->assertSame($this->query->get(), $query->get());
        $this->assertTrue($query->sameValueAs($this->query));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailMergeWith()
    {
        $this->query->mergeWith(new StdClass());
    }

    public function testgetData()
    {
        $this->assertSame('toto', $this->query->getData('kingkong'));
    }

    public function testgetDataWithDefaultValue()
    {
        $expected = 'toofan';
        $this->assertSame($expected, $this->query->getData('togo', $expected));
    }

    public function testhAsKey()
    {
        $this->assertFalse($this->query->hasKey('togo'));
        $this->assertTrue($this->query->hasKey('kingkong'));
    }

    public function testCountable()
    {
        $this->assertSame(1, count($this->query));
    }

    public function testKeys()
    {
        $query = Query::createFromArray([
            'foo' => 'bar',
            'baz' => 'troll',
            'lol' => 3,
            'toto' => 'troll'
        ]);
        $this->assertCount(0, $query->getKeys('foo'));
        $this->assertSame(['foo'], $query->getKeys('bar'));
        $this->assertCount(1, $query->getKeys('3'));
        $this->assertSame(['lol'], $query->getKeys('3'));
        $this->assertSame(['baz', 'toto'], $query->getKeys('troll'));
    }

    public function testDotFromString()
    {
        $query = new Query('foo.bar=baz');
        $this->assertSame('foo.bar=baz', (string) $query);
    }

    public function testDotFromArray()
    {
        $query = Query::createFromArray(['foo.bar' => 'baz']);
        $this->assertSame('foo.bar=baz', (string) $query);
    }

    public function testStringWithoutContent()
    {
        $query = new Query('foo&bar&baz');

        $this->assertCount(3, $query->getKeys());
        $this->assertSame(['foo', 'bar', 'baz'], $query->getKeys());
        $this->assertSame('', $query->getData('foo'));
        $this->assertSame('', $query->getData('bar'));
        $this->assertSame('', $query->getData('baz'));
    }

    public function testToArray()
    {
        $expected = ['foo' => '', 'bar' => '', 'baz' => '', 'to.go' => 'toofan'];
        $query = new Query('foo&bar&baz&to.go=toofan');
        $this->assertSame($expected, $query->toArray());
        $this->assertSame($expected, json_decode(json_encode($query), true));
    }

    public function testDebugInfo()
    {
        $query = new Query('foo&bar&baz&to.go=toofan');

        $expected = [
            'data' => 'foo=&bar=&baz=&to.go=toofan',
        ];

        $this->assertSame($expected, $query->__debugInfo());
    }
}

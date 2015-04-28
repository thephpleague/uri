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
        $this->query = new Query('kingkong=toto');
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

    /**
     * @param $input
     * @param $expected
     * @dataProvider validMergeValue
     */
    public function testMergerWith($input, $expected)
    {
        $query = $this->query->mergeWith($input);
        $this->assertSame($expected, (string) $query);
    }

    public function validMergeValue()
    {
        return [
            'array' => [
                ['john' => 'doe the john'],
                'kingkong=toto&john=doe%20the%20john',
            ],
            'iterator' => [
                new ArrayIterator(['john' => 'doe the john']),
                'kingkong=toto&john=doe%20the%20john'
            ],
            'QueryInterface' => [
                Query::createFromArray(['foo' => 'bar']),
                'kingkong=toto&foo=bar',
            ],
            'string' => [
                'kingkong=tata',
                'kingkong=tata',
            ],
            'empty string' => [
                '',
                'kingkong=toto',
            ],
            'null value' => [
                null,
                'kingkong=toto',
            ],
            'remove parameter' => [
                ['kingkong' => null],
                '',
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFailMergeWith()
    {
        $this->query->mergeWith(new StdClass());
    }

    public function testGetParameter()
    {
        $this->assertSame('toto', $this->query->getParameter('kingkong'));
    }

    public function testGetParameterWithDefaultValue()
    {
        $expected = 'toofan';
        $this->assertSame($expected, $this->query->getParameter('togo', $expected));
    }

    public function testHasKey()
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
        $this->assertSame('', $query->getParameter('foo'));
        $this->assertSame('', $query->getParameter('bar'));
        $this->assertSame('', $query->getParameter('baz'));
    }

    public function testToArray()
    {
        $expected = ['foo' => '', 'bar' => '', 'baz' => '', 'to.go' => 'toofan'];
        $query = new Query('foo&bar&baz&to.go=toofan');
        $this->assertSame($expected, $query->toArray());
        $this->assertSame($expected, json_decode(json_encode($query), true));
    }

    public function invalidQueryStrings()
    {
        return [
            'true'      => [ true ],
            'false'     => [ false ],
            'array'     => [ [ 'baz=bat' ] ],
            'object'    => [ (object) [ 'baz=bat' ] ],
        ];
    }

    /**
     * @dataProvider invalidQueryStrings
     */
    public function testWithQueryRaisesExceptionForInvalidQueryStrings($query)
    {
        $this->setExpectedException('InvalidArgumentException', 'Data passed must be a valid string;');
        new Query($query);
    }
}

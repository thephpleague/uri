<?php

namespace League\Uri\test\Components;

use ArrayIterator;
use InvalidArgumentException;
use League\Uri\Components\Query;
use PHPUnit_Framework_TestCase;

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

    /**
     * @param $str
     * @expectedException InvalidArgumentException
     * @dataProvider failedConstructor
     */
    public function testFailedConstructor($str)
    {
        new Query($str);
    }

    public function failedConstructor()
    {
        return [
            'bool'      => [true],
            'Std Class' => [(object) 'foo'],
            'float'     => [1.2],
            'array'      => [['foo']],
        ];
    }

    public function testIterator()
    {
        $this->assertSame(['kingkong' => 'toto'], iterator_to_array($this->query, true));
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
     * @param $input
     * @expectedException \InvalidArgumentException
     * @dataProvider createFromArrayFailedProvider
     */
    public function testCreateFromArrayFailed($input)
    {
        Query::createFromArray($input);
    }

    public function createFromArrayFailedProvider()
    {
        return [
            'Non traversable object' => [new \StdClass()],
            'String' => ['toto=23'],
            'reserved character # used as value' => [['toto' => '#23']],
            'reserved character # used as key' => [['tot#o' => '23']],
        ];
    }

    public function testSameValueAs()
    {
        $empty_query = new Query();
        $this->assertFalse($empty_query->sameValueAs($this->query));
        $query = $empty_query->merge($this->query);
        $this->assertInstanceof('League\Uri\Interfaces\Components\Query', $query);
        $this->assertTrue($query->sameValueAs($this->query));
    }

    /**
     * @param $input
     * @param $expected
     * @dataProvider validMergeValue
     */
    public function testMerge($input, $expected)
    {
        $query = $this->query->merge($input);
        $this->assertSame($expected, (string) $query);
    }

    public function validMergeValue()
    {
        return [
            'with new data' => [
                Query::createFromArray(['john' => 'doe the john']),
                'kingkong=toto&john=doe%20the%20john',
            ],
            'with the same data' => [
                new Query('kingkong=toto'),
                'kingkong=toto',
            ],
            'without new data' => [
                new Query(''),
                'kingkong=toto',
            ],
            'with array' => [
                ['john' => 'doe the john'],
                'kingkong=toto&john=doe%20the%20john',
            ],
            'with empty array' => [
                [],
                'kingkong=toto',
            ],
            'with string' => [
                'foo=bar',
                'kingkong=toto&foo=bar',
            ],
            'with empty string' => [
                '',
                'kingkong=toto',
            ],
        ];
    }

    public function testGetParameter()
    {
        $this->assertSame('toto', $this->query->getValue('kingkong'));
    }

    public function testGetParameterWithDefaultValue()
    {
        $expected = 'toofan';
        $this->assertSame($expected, $this->query->getValue('togo', $expected));
    }

    public function testhasKey()
    {
        $this->assertTrue($this->query->hasKey('kingkong'));
        $this->assertFalse($this->query->hasKey('togo'));
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
            'toto' => 'troll',
        ]);
        $this->assertCount(0, $query->keys('foo'));
        $this->assertSame(['foo'], $query->keys('bar'));
        $this->assertCount(1, $query->keys('3'));
        $this->assertSame(['lol'], $query->keys('3'));
        $this->assertSame(['baz', 'toto'], $query->keys('troll'));
    }

    public function testStringWithoutContent()
    {
        $query = new Query('foo&bar&baz');

        $this->assertCount(3, $query->keys());
        $this->assertSame(['foo', 'bar', 'baz'], $query->keys());
        $this->assertSame(null, $query->getValue('foo'));
        $this->assertSame(null, $query->getValue('bar'));
        $this->assertSame(null, $query->getValue('baz'));
    }

    public function testToArray()
    {
        $expected = ['foo' => null, 'bar' => null, 'baz' => null, 'to.go' => 'toofan'];
        $query = new Query('foo&bar&baz&to.go=toofan');
        $this->assertSame($expected, $query->toArray());
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
        $this->assertSame($result, (string) (new Query($origin))->without($without));
    }

    public function withoutProvider()
    {
        return [
            ['foo&bar&baz&to.go=toofan', ['foo', 'to.go'], 'bar&baz'],
            ['foo&bar&baz&to.go=toofan', ['foo', 'unknown'], 'bar&baz&to.go=toofan'],
            ['foo&bar&baz&to.go=toofan', function ($value) {
                return strpos($value, 'b') !== false;
            }, 'foo&to.go=toofan'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithoutFaild()
    {
        (new Query('toofan=orobo'))->without('toofan');
    }

    /**
     * @param $params
     * @param $callable
     * @param $expected
     * @dataProvider filterProvider
     */
    public function testFilter($params, $callable, $expected)
    {
        $this->assertSame($expected, (string) Query::createFromArray($params)->filter($callable, Query::FILTER_USE_VALUE));
    }

    public function filterProvider()
    {
        $func = function ($value) {
            return stripos($value, '.') !== false;
        };

        return [
            'empty query'  => [[], $func, ''],
            'remove One'   => [['toto' => 'foo.bar', 'zozo' => 'stay'], $func, 'toto=foo.bar'],
            'remove All'   => [['to.to' => 'foobar', 'zozo' => 'stay'], $func, ''],
            'remove None'  => [['toto' => 'foo.bar', 'zozo' => 'st.ay'], $func, 'toto=foo.bar&zozo=st.ay'],
        ];
    }

    /**
     * @param $params
     * @param $callable
     * @param $expected
     * @dataProvider filterByOffsetsProvider
     */
    public function testFilterOffsets($params, $callable, $expected)
    {
        $this->assertSame($expected, (string) Query::createFromArray($params)->filter($callable, Query::FILTER_USE_KEY));
    }

    public function filterByOffsetsProvider()
    {
        $func = function ($value) {
            return stripos($value, '.') !== false;
        };

        return [
            'empty query'  => [[], $func, ''],
            'remove One'   => [['toto' => 'foo.bar', 'zozo' => 'stay'], $func, ''],
            'remove All'   => [['to.to' => 'foobar', 'zozo' => 'stay'], $func, 'to.to=foobar'],
            'remove None'  => [['to.to' => 'foo.bar', 'zo.zo' => 'st.ay'], $func, 'to.to=foo.bar&zo.zo=st.ay'],
        ];
    }

    /**
     * @dataProvider invalidFilter
     * @expectedException InvalidArgumentException
     * @param $callable
     * @param $flag
     */
    public function testFilterOffsetsFailed($callable, $flag)
    {
        Query::createFromArray([])->filter($callable, $flag);
    }

    public function invalidFilter()
    {
        $callback = function () {
            return true;
        };

        return [[$callback, 'toto']];
    }

    /**
     * @dataProvider invalidQueryStrings
     * @expectedException InvalidArgumentException
     * @param $query
     */
    public function testWithQueryRaisesExceptionForInvalidQueryStrings($query)
    {
        new Query($query);
    }

    public function invalidQueryStrings()
    {
        return [
            'true'      => [ true ],
            'false'     => [ false ],
            'array'     => [ [ 'baz=bat' ] ],
        ];
    }

    /**
     * @param $data
     * @param $sort
     * @param $expected
     * @dataProvider ksortProvider
     */
    public function testksort($data, $sort, $expected)
    {
        $this->assertSame($expected, Query::createFromArray($data)->ksort($sort)->toArray());
    }

    public function ksortProvider()
    {
        return [
            [
                ['superman' => 'lex luthor', 'batman' => 'joker'],
                SORT_REGULAR,
                [ 'batman' => 'joker', 'superman' => 'lex luthor'],
            ],
            [
                ['superman' => 'lex luthor', 'batman' => 'joker'],
                function ($dataA, $dataB) {
                    return strcasecmp($dataA, $dataB);
                },
                [ 'batman' => 'joker', 'superman' => 'lex luthor'],
            ],
            [
                ['superman' => 'lex luthor', 'superwoman' => 'joker'],
                function ($dataA, $dataB) {
                    return strcasecmp($dataA, $dataB);
                },
                ['superman' => 'lex luthor', 'superwoman' => 'joker'],
            ],
        ];
    }
}

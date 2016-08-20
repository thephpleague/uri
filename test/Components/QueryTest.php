<?php

namespace League\Uri\Test\Components;

use ArrayIterator;
use InvalidArgumentException;
use League\Uri\Components\Query;
use League\Uri\Interfaces;
use League\Uri\Test\AbstractTestCase;

/**
 * @group query
 */
class QueryTest extends AbstractTestCase
{
    /**
     * @var Query
     */
    protected $query;

    protected function setUp()
    {
        $this->query = new Query('kingkong=toto');
    }

    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $this->assertInternalType('array', $this->query->__debugInfo());
        ob_start();
        var_dump($this->query);
        $res = ob_get_clean();
        $this->assertContains($this->query->__toString(), $res);
        $this->assertContains('query', $res);
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
            'bool' => [true],
            'Std Class' => [(object) 'foo'],
            'float' => [1.2],
            'array' => [['foo']],
            'reserved char' => ['foo#bar'],
        ];
    }

    public function testIterator()
    {
        $this->assertSame(['kingkong' => 'toto'], iterator_to_array($this->query, true));
    }

    /**
     * @dataProvider queryProvider
     */
    public function testGetUriComponent($input, $expected)
    {
        $query = is_array($input) ? Query::createFromArray($input) : new Query($input);

        $this->assertSame($expected, $query->getUriComponent());
    }

    public function queryProvider()
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';

        return [
            'string' => ['kingkong=toto', '?kingkong=toto'],
            'null' => [null, ''],
            'empty string' => ['', '?'],
            'empty array' => [[], ''],
            'non empty array' => [['' => null], '?'],
            'contains a reserved word #' => ['foo%23bar', '?foo%23bar'],
            'contains a delimiter ?' => ['?foo%23bar', '??foo%23bar'],
            'key-only' => ['k^ey', '?k%5Eey'],
            'key-value' => ['k^ey=valu`', '?k%5Eey=valu%60'],
            'array-key-only' => ['key[]', '?key%5B%5D'],
            'array-key-value' => ['key[]=valu`', '?key%5B%5D=valu%60'],
            'complex' => ['k^ey&key[]=valu`&f<>=`bar', '?k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'],
            'Percent encode spaces' => ['q=va lue', '?q=va%20lue'],
            'Percent encode multibyte' => ['€', '?%E2%82%AC'],
            "Don't encode something that's already encoded" => ['q=va%20lue', '?q=va%20lue'],
            'Percent encode invalid percent encodings' => ['q=va%2-lue', '?q=va%252-lue'],
            "Don't encode path segments" => ['q=va/lue', '?q=va/lue'],
            "Don't encode unreserved chars or sub-delimiters" => [$unreserved, '?'.$unreserved],
            'Encoded unreserved chars are not decoded' => ['q=v%61lue', '?q=v%61lue'],
        ];
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
            'Non traversable object' => [new \stdClass()],
            'String' => ['toto=23'],
        ];
    }

    public function testSameValueAs()
    {
        $empty_query = new Query();
        $this->assertFalse($empty_query->sameValueAs($this->query));
        $query = $empty_query->merge($this->query);
        $this->assertInstanceOf(Interfaces\Query::class, $query);
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
        ];
    }

    /**
     * @dataProvider filterProvider
     *
     * @param array    $params
     * @param callable $callable
     * @param int      $flag
     * @param string   $expected
     */
    public function testFilter($params, $callable, $flag, $expected)
    {
        $this->assertSame($expected, (string) Query::createFromArray($params)->filter($callable, $flag));
    }

    public function filterProvider()
    {
        $func = function ($value) {
            return stripos($value, '.') !== false;
        };

        $funcBoth = function ($value, $key) {
            return strpos($value, 'o') !== false && strpos($key, 'o') !== false;
        };

        return [
            'empty query' => [[], $func, Query::FILTER_USE_VALUE, ''],
            'remove One' => [['toto' => 'foo.bar', 'zozo' => 'stay'], $func, Query::FILTER_USE_VALUE, 'toto=foo.bar'],
            'remove All' => [['to.to' => 'foobar', 'zozo' => 'stay'], $func, Query::FILTER_USE_VALUE, ''],
            'remove None' => [['toto' => 'foo.bar', 'zozo' => 'st.ay'], $func, Query::FILTER_USE_VALUE, 'toto=foo.bar&zozo=st.ay'],
            'remove with filter both' => [['toto' => 'foo', 'foo' => 'bar'], $funcBoth, Query::FILTER_USE_BOTH, 'toto=foo'],
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
            'empty query' => [[], $func, ''],
            'remove One' => [['toto' => 'foo.bar', 'zozo' => 'stay'], $func, ''],
            'remove All' => [['to.to' => 'foobar', 'zozo' => 'stay'], $func, 'to.to=foobar'],
            'remove None' => [['to.to' => 'foo.bar', 'zo.zo' => 'st.ay'], $func, 'to.to=foo.bar&zo.zo=st.ay'],
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
            'true' => [ true ],
            'false' => [ false ],
            'array' => [ [ 'baz=bat' ] ],
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

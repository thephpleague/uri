<?php

namespace League\Uri\Test\Modifiers;

use League\Uri\Components\Query;
use League\Uri\Modifiers\FilterQuery;
use League\Uri\Modifiers\KsortQuery;
use League\Uri\Modifiers\MergeQuery;
use League\Uri\Modifiers\RemoveQueryKeys;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group query
 * @group modifier
 */
class QueryModifierTest extends PHPUnit_Framework_TestCase
{
    private $uri;

    public function setUp()
    {
        $this->uri = HttpUri::createFromString(
            'http://www.example.com/path/to/the/sky.php?kingkong=toto&foo=bar+baz#doc3'
        );
    }

    public function testFilterQueryParameters()
    {
        $modifier = (new FilterQuery(function ($value) {
            return $value == 'kingkong';
        }, Query::FILTER_USE_VALUE))->withFlag(Query::FILTER_USE_KEY);

        $this->assertSame('kingkong=toto', $modifier->__invoke($this->uri)->getQuery());
    }

    public function testFilterQueryValues()
    {
        $filter = function ($value) {
            return $value == 'toto';
        };

        $modifier = (new FilterQuery($filter, Query::FILTER_USE_VALUE))->withCallable($filter);

        $this->assertSame('kingkong=toto', $modifier->__invoke($this->uri)->getQuery());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFilterFlagFailed()
    {
        new FilterQuery(function ($value) {
            return $value == 'toto';
        }, 'toto');
    }

    /**
     * @dataProvider validQueryProvider
     */
    public function testMergeQuery($query, $expected)
    {
        $modifier = (new MergeQuery($query))->withQuery($query);

        $this->assertSame($expected, $modifier->__invoke($this->uri)->getQuery());
    }

    public function validQueryProvider()
    {
        return [
            ['toto', 'kingkong=toto&foo=bar%20baz&toto'],
            ['toto=&toto=1', 'kingkong=toto&foo=bar%20baz&toto=&toto=1'],
        ];
    }

    /**
     * @dataProvider validQueryKsortProvider
     */
    public function testKsortQuery($input, $expected)
    {
        $modifier = (new KsortQuery($input))->withAlgorithm($input);

        $this->assertSame($expected, $modifier->__invoke($this->uri)->getQuery());
    }

    //?kingkong=toto&foo=bar+baz

    public function validQueryKsortProvider()
    {
        return [
            [SORT_REGULAR, 'foo=bar%20baz&kingkong=toto'],
            [function ($value1, $value2) {
                return strcasecmp($value1, $value2);
            }, 'foo=bar%20baz&kingkong=toto'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testKsortQueryFailed()
    {
        new KsortQuery(['data']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMergeQueryContructorFailed()
    {
        new MergeQuery(new Query('toto=king'));
    }

    /**
     * @dataProvider validWithoutQueryValuesProvider
     */
    public function testWithoutQueryValuesProcess($input, $expected)
    {
        $modifier = (new RemoveQueryKeys($input))->withKeys($input);

        $this->assertSame($expected, $modifier->__invoke($this->uri)->getQuery());
    }

    public function validWithoutQueryValuesProvider()
    {
        return [
            [[1], 'kingkong=toto&foo=bar%20baz'],
        ];
    }
}

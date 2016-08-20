<?php

namespace League\Uri\Test;

use League\Uri\QueryParser;
use PHPUnit_Framework_TestCase;

/**
 * @group parser
 */
class QueryParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var QueryParser
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new QueryParser();
    }

    /**
     * @dataProvider parserProvider
     *
     * @param string $query
     * @param string $separator
     * @param int    $encoding
     * @param array  $expected
     */
    public function testParse($query, $separator, $encoding, $expected)
    {
        $this->assertSame($expected, $this->parser->parse($query, $separator, $encoding));
    }

    public function parserProvider()
    {
        return [
            'empty string' => ['', '&', PHP_QUERY_RFC3986, []],
            'identical keys' => ['a=1&a=2', '&', PHP_QUERY_RFC3986, ['a' => ['1', '2']]],
            'no value' => ['a&b', '&', PHP_QUERY_RFC3986, ['a' => null, 'b' => null]],
            'empty value' => ['a=&b=', '&', PHP_QUERY_RFC3986, ['a' => '', 'b' => '']],
            'php array' => ['a[]=1&a[]=2', '&', PHP_QUERY_RFC3986, ['a[]' => ['1', '2']]],
            'preserve dot' => ['a.b=3', '&', PHP_QUERY_RFC3986, ['a.b' => '3']],
            'decode' => ['a%20b=c%20d', '&', PHP_QUERY_RFC3986, ['a b' => 'c d']],
            'no key stripping' => ['a=&b', '&', PHP_QUERY_RFC3986, ['a' => '', 'b' => null]],
            'no value stripping' => ['a=b=', '&', PHP_QUERY_RFC3986, ['a' => 'b=']],
            'key only' => ['a', '&', PHP_QUERY_RFC3986, ['a' => null]],
            'preserve falsey 1' => ['0', '&', PHP_QUERY_RFC3986, ['0' => null]],
            'preserve falsey 2' => ['0=', '&', PHP_QUERY_RFC3986, ['0' => '']],
            'preserve falsey 3' => ['a=0', '&', PHP_QUERY_RFC3986, ['a' => '0']],
            'no encoding' => ['a=0&toto=le+heros', '&', false, ['a' => '0', 'toto' => 'le heros']],
            'legacy encoding' => ['john+doe=bar&a=0', '&', PHP_QUERY_RFC1738, ['john doe' => 'bar', 'a' => '0']],
            'different separator' => ['a=0;b=0&c=4', ';', false, ['a' => '0', 'b' => '0&c=4']],
            'numeric key only' => ['42', '&', PHP_QUERY_RFC3986, ['42' => null]],
            'numeric key' => ['42=l33t', '&', PHP_QUERY_RFC3986, ['42' => 'l33t']],
        ];
    }

    /**
     * @param $query
     * @param $expected
     * @dataProvider buildProvider
     */
    public function testBuild($query, $expected)
    {
        $this->assertSame($expected, $this->parser->build($query, '&', false));
    }

    public function buildProvider()
    {
        return [
            'empty string' => [[], ''],
            'identical keys' => [['a' => ['1', '2']], 'a=1&a=2'],
            'no value' => [['a' => null, 'b' => null], 'a&b'],
            'empty value' => [['a' => '', 'b' => ''], 'a=&b='],
            'php array' => [['a[]' => ['1', '2']], 'a[]=1&a[]=2'],
            'preserve dot' => [['a.b' => '3'], 'a.b=3'],
            'no key stripping' => [['a' => '', 'b' => null], 'a=&b'],
            'no value stripping' => [['a' => 'b='], 'a=b='],
            'key only' => [['a' => null], 'a'],
            'preserve falsey 1' => [['0' => null], '0'],
            'preserve falsey 2' => [['0' => ''], '0='],
            'preserve falsey 3' => [['a' => '0'], 'a=0'],
        ];
    }

    public function testFailSafeQueryParsing()
    {
        $arr = ['a' => '1', 'b' => 'le heros'];
        $expected = 'a=1&b=le%20heros';

        $this->assertSame($expected, $this->parser->build($arr, '&', 'yolo'));
    }

    public function testParserBuilderPreserveQuery()
    {
        $querystring = 'uri=http://example.com?a=b%26c=d';
        $data = $this->parser->parse($querystring);
        $this->assertSame([
            'uri' => 'http://example.com?a=b&c=d',
        ], $data);
        $this->assertSame($querystring, $this->parser->build($data));
    }
}

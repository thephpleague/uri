<?php

namespace League\Uri\test;

use InvalidArgumentException;
use League\Uri\Parser;
use PHPUnit_Framework_TestCase;

/**
 * @group parser
 */
class ParserTest extends PHPUnit_Framework_TestCase
{
    protected $parser;

    public function setUp()
    {
        $this->parser = new Parser();
    }

    /**
     * @dataProvider testValidURI
     * @param $uri
     * @param $expected
     */
    public function testParseSucced($uri, $expected)
    {
        $this->assertSame($expected, $this->parser->parseUri($uri));
    }

    public function testValidURI()
    {
        return [
            'complete URI' => [
                'scheme://user:pass@host:81/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'host',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI is not normalized' =>  [
                'ScheMe://user:pass@HoSt:81/path?query#fragment',
                [
                    'scheme' => 'ScheMe',
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without scheme' =>  [
                '//user:pass@HoSt:81/path?query#fragment',
                [
                    'scheme' => null,
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without userinfo' =>  [
                'scheme://HoSt:81/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI with empty userinfo' =>  [
                'scheme://@HoSt:81/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => '',
                    'pass' => null,
                    'host' => 'HoSt',
                    'port' => 81,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without port' => [
                'scheme://user:pass@host/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => 'user',
                    'pass' => 'pass',
                    'host' => 'host',
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without user info and port' => [
                'scheme://host/path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => 'host',
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without authority' => [
                'scheme:path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without authority and scheme' => [
                '/path',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/path',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI with empty host' => [
                'scheme:///path?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => '',
                    'port' => null,
                    'path' => '/path',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without path' => [
                'scheme://host?query#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => 'host',
                    'port' => null,
                    'path' => '',
                    'query' => 'query',
                    'fragment' => 'fragment',
                ],
            ],
            'URI without query' => [
                'scheme:path#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
            'URI with empty query' => [
                'scheme:path?#fragment',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => '',
                    'fragment' => 'fragment',
                ],
            ],
            'URI with query only' => [
                '?query',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '',
                    'query' => 'query',
                    'fragment' => null,
                ],
            ],
            'URI without fragment' => [
                'scheme:path',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'URI with empty fragment' => [
                'scheme:path#',
                [
                    'scheme' => 'scheme',
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => '',
                ],
            ],
            'URI with fragment only' => [
                '#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
            'URI without authority 2' => [
                'path#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'path',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
        ];
    }

    /**
     * @dataProvider testInvalidURI
     * @expectedException InvalidArgumentException
     * @param $uri
     */
    public function testParseFailed($uri)
    {
        $this->parser->parseUri($uri);
    }

    public function testInvalidURI()
    {
        return [
            'invalid port' => ['scheme://host:port/path?query#fragment'],
            'invalid host' => ['scheme://[127.0.0.1]/path?query#fragment'],
            'invalid ipv6 host' => ['scheme://[::1]./path?query#fragment'],
            'invalid host too long' => ['scheme://'.implode('.', array_fill(0, 128, 'a'))],
            'invalid scheme' => ['0scheme://host/path?query#fragment'],
        ];
    }

    /**
     * @param  $query
     * @param  $expected
     * @dataProvider parserProvider
     */
    public function testParse($query, $separator, $encoding, $expected)
    {
        $this->assertSame($expected, $this->parser->parseQuery($query, $separator, $encoding));
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
        ];
    }

    /**
     * @param $query
     * @param $expected
     * @dataProvider buildProvider
     */
    public function testBuild($query, $expected)
    {
        $this->assertSame($expected, $this->parser->buildQuery($query, '&', false));
    }

    public function buildProvider()
    {
        return [
            'empty string'       => [[], ''],
            'identical keys'     => [['a' => ['1', '2']], 'a=1&a=2'],
            'no value'           => [['a' => null, 'b' => null], 'a&b'],
            'empty value'        => [['a' => '', 'b' => ''], 'a=&b='],
            'php array'          => [['a[]' => ['1', '2']], 'a[]=1&a[]=2'],
            'preserve dot'       => [['a.b' => '3'], 'a.b=3'],
            'no key stripping'   => [['a' => '', 'b' => null], 'a=&b'],
            'no value stripping' => [['a' => 'b='], 'a=b='],
            'key only'           => [['a' => null], 'a'],
            'preserve falsey 1'  => [['0' => null], '0'],
            'preserve falsey 2'  => [['0' => ''], '0='],
            'preserve falsey 3'  => [['a' => '0'], 'a=0'],
        ];
    }
}

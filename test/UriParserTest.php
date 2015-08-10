<?php

namespace League\Uri\test;

use InvalidArgumentException;
use League\Uri\UriParser;
use PHPUnit_Framework_TestCase;

/**
 * @group parser
 */
class UriParserTest extends PHPUnit_Framework_TestCase
{
    protected $parser;

    public function setUp()
    {
        $this->parser = new UriParser();
    }

    /**
     * @dataProvider testValidURI
     * @param $uri
     * @param $expected
     */
    public function testParseSucced($uri, $expected)
    {
        $this->assertSame($expected, $this->parser->parse($uri));
    }

    /**
     * @dataProvider testValidURI
     * @param $uri
     * @param $expected
     */
    public function testBuildSucced($expected, $components)
    {
        $this->assertSame($expected, $this->parser->build($components));
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
            'URI with emtpy query and fragment' => [
                '?#',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
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
        $this->parser->parse($uri);
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
     * @dataProvider testInvalidComponents
     * @expectedException InvalidArgumentException
     * @param $components
     */
    public function testBuildThrowInvalidArgumentException($components)
    {
        $this->parser->build($components);
    }

    public function testInvalidComponents()
    {
        return [
            'invalid query' => [[
                'scheme' => null,
                'user' => null,
                'pass' => null,
                'host' => null,
                'port' => null,
                'path' => 'path',
                'query' => 'yo#lo',
                'fragment' => 'fragment',
            ]],
            'invalid path with query' => [[
                'scheme' => null,
                'user' => null,
                'pass' => null,
                'host' => null,
                'port' => null,
                'path' => 'pa?th',
                'query' => 'query',
                'fragment' => 'fragment',
            ]],
            'invalid path with fragment' => [[
                'scheme' => null,
                'user' => null,
                'pass' => null,
                'host' => null,
                'port' => null,
                'path' => 'pa#th',
                'query' => 'query',
                'fragment' => 'fragment',
            ]],
            'invalid user component' => [[
                'scheme' => 'scheme',
                'user' => 'user:pass',
                'pass' => null,
                'host' => null,
                'port' => null,
                'path' => 'path',
                'query' => 'query',
                'fragment' => 'fragment',
            ]],
            'invalid pass' => [[
                'scheme' => 'scheme',
                'user' => 'user',
                'pass' => 'pass?yeah',
                'host' => null,
                'port' => null,
                'path' => 'path',
                'query' => 'query',
                'fragment' => 'fragment',
            ]],
        ];
    }

    /**
     * @dataProvider testBuildFailedProvider
     * @expectedException RuntimeException
     */
    public function testBuildThrowRuntimeException($components)
    {
        $this->parser->build($components);
    }

    public function testBuildFailedProvider()
    {
        return [
            'invalid path' => [[
                'scheme' => null,
                'user' => null,
                'pass' => null,
                'host' => null,
                'port' => null,
                'path' => 'pa:th',
                'query' => 'query',
                'fragment' => 'fragment',
            ]],
        ];
    }
}

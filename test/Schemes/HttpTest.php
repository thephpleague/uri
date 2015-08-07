<?php

namespace League\Uri\test\Schemes;

use InvalidArgumentException;
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group http
 */
class HttpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $expected
     * @param $input
     * @dataProvider validServerArray
     */
    public function testCreateFromServer($expected, $input)
    {
        $this->assertSame($expected, HttpUri::createFromServer($input)->__toString());
    }

    public function validServerArray()
    {
        return [
            'with host' => [
                'https://example.com:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'HTTP_HOST' => 'example.com',
                ],
            ],
            'server address IPv4' => [
                'https://127.0.0.1:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                ],
            ],
            'server address IPv6' => [
                'https://[::1]:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '::1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                ],
            ],
            'with port attached to host' => [
                'https://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 80,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'with standard apache HTTP server' => [
                'http://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => '',
                    'SERVER_PORT' => 80,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'with IIS HTTP server' => [
                'http://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'off',
                    'SERVER_PORT' => 80,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'with standard port setting' => [
                'https://localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'HTTP_HOST' => 'localhost',
                ],
            ],
            'without port' => [
                'https://localhost',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'HTTP_HOST' => 'localhost',
                ],
            ],
            'with user info' => [
                'https://foo:bar@localhost:23',
                [
                    'PHP_SELF' => '',
                    'REQUEST_URI' => '',
                    'SERVER_ADDR' => '127.0.0.1',
                    'PHP_AUTH_USER' => 'foo',
                    'PHP_AUTH_PW' => 'bar',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'HTTP_HOST' => 'localhost:23',
                ],
            ],
            'without request uri' => [
                'https://127.0.0.1:23/toto?foo=bar',
                [
                    'PHP_SELF' => '/toto',
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                    'QUERY_STRING' => 'foo=bar',
                ],
            ],
            'without request uri and server host' => [
                'https://127.0.0.1:23',
                [
                    'SERVER_ADDR' => '127.0.0.1',
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 23,
                ],
            ],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailCreateFromServerWithoutHost()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
        ];

        HttpUri::createFromServer($server);
    }

    /**
     * @dataProvider validUrlArray
     * @param $expected
     * @param $input
     */
    public function testCreateFromString($expected, $input)
    {
        $this->assertSame($expected, HttpUri::createFromString($input)->__toString());
    }

    public function validUrlArray()
    {
        return [
            'with default port' => [
                'http://example.com/foo/bar?foo=bar#content',
                'http://example.com:80/foo/bar?foo=bar#content',
            ],
            'without scheme' => [
                '//example.com',
                '//example.com',
            ],
            'without scheme but with port' => [
                '//example.com:80',
                '//example.com:80',
            ],
            'with user info' => [
                'http://login:pass@example.com/',
                'http://login:pass@example.com/',
            ],
            'empty string' => [
                '',
                '',
            ],
        ];
    }

    /**
     * @dataProvider isValidProvider
     * @expectedException RuntimeException
     * @param $input
     */
    public function testIsValid($input)
    {
        HttpUri::createFromString($input);
    }

    public function isValidProvider()
    {
        return [
            ['ftp:example.com'],
            ['wss:/example.com'],
        ];
    }

    /**
     * @dataProvider resolveProvider
     * @param $uri
     * @param $relative
     * @param $expected
     */
    public function testResolve($uri, $relative, $expected)
    {
        $uri      = HttpUri::createFromString($uri);
        $relative = HttpUri::createFromString($relative);

        $this->assertSame($expected, (string) $uri->resolve($relative));
    }

    public function resolveProvider()
    {
        $base_uri = 'http://a/b/c/d;p?q';

        return [
          'base uri' =>                 [$base_uri, '',               $base_uri],
          'scheme' =>                  [$base_uri, 'http://d/e/f',   'http://d/e/f'],
          'path 1' =>                  [$base_uri, 'g',              'http://a/b/c/g'],
          'path 2' =>                  [$base_uri, './g',            'http://a/b/c/g'],
          'path 3' =>                  [$base_uri, 'g/',             'http://a/b/c/g/'],
          'path 4' =>                  [$base_uri, '/g',             'http://a/g'],
          'authority' =>               [$base_uri, '//g',            'http://g'],
          'query' =>                   [$base_uri, '?y',             'http://a/b/c/d;p?y'],
          'path + query' =>            [$base_uri, 'g?y',            'http://a/b/c/g?y'],
          'fragment' =>                [$base_uri, '#s',             'http://a/b/c/d;p?q#s'],
          'path + fragment' =>         [$base_uri, 'g#s',            'http://a/b/c/g#s'],
          'path + query + fragment' => [$base_uri, 'g?y#s',          'http://a/b/c/g?y#s'],
          'single dot 1' =>            [$base_uri, '.',              'http://a/b/c/'],
          'single dot 2' =>            [$base_uri, './',             'http://a/b/c/'],
          'single dot 3' =>            [$base_uri, './g/.',          'http://a/b/c/g/'],
          'single dot 4' =>            [$base_uri, 'g/./h',          'http://a/b/c/g/h'],
          'double dot 1' =>            [$base_uri, '..',             'http://a/b/'],
          'double dot 2' =>            [$base_uri, '../',            'http://a/b/'],
          'double dot 3' =>            [$base_uri, '../g',           'http://a/b/g'],
          'double dot 4' =>            [$base_uri, '../..',          'http://a/'],
          'double dot 5' =>            [$base_uri, '../../',         'http://a/'],
          'double dot 6' =>            [$base_uri, '../../g',        'http://a/g'],
          'double dot 7' =>            [$base_uri, '../../../g',     'http://a/g'],
          'double dot 8' =>            [$base_uri, '../../../../g',  'http://a/g'],
          'double dot 9' =>            [$base_uri, 'g/../h' ,        'http://a/b/c/h'],
          'mulitple slashes' =>        [$base_uri, 'foo////g',       'http://a/b/c/foo////g'],
          'complex path 1' =>          [$base_uri, ';x',             'http://a/b/c/;x'],
          'complex path 2' =>          [$base_uri, 'g;x',            'http://a/b/c/g;x'],
          'complex path 3' =>          [$base_uri, 'g;x?y#s',        'http://a/b/c/g;x?y#s'],
          'complex path 4' =>          [$base_uri, 'g;x=1/./y',      'http://a/b/c/g;x=1/y'],
          'complex path 5' =>          [$base_uri, 'g;x=1/../y',     'http://a/b/c/y'],
          'origin uri without path' => ['http://h:b@a', 'b/../y',    'http://h:b@a/y'],
          '2 relative paths 1'      => ['a/b',          '../..',     '/'],
          '2 relative paths 2'      => ['a/b',          './.',       'a/'],
          '2 relative paths 3'      => ['a/b',          '../c',      'c'],
          '2 relative paths 4'      => ['a/b',          'c/..',      'a/'],
          '2 relative paths 5'      => ['a/b',          'c/.',       'a/c/'],
        ];
    }

    /**
     * @dataProvider resolveUriProvider
     * @param $uri1
     * @param $uri2
     */
    public function testResolveUri($uri1, $uri2)
    {
        $this->assertSame($uri2, $uri1->resolve($uri2));
    }

    public function resolveUriProvider()
    {
        return [
            'two hierarchical uri' => [
                HttpUri::createFromString('http://example.com/path/to/file'),
                FtpUri::createFromString('//a/b/c/d;p'),
            ],
            'hierarchical uri resolve opaque uri' => [
                HttpUri::createFromString('http://example.com/path/to/file'),
                DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21'),
            ],
        ];
    }
}

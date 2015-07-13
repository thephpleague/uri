<?php

namespace League\Uri\Test;

use League\Uri\Fragment;
use League\Uri\Host;
use League\Uri\Pass;
use League\Uri\Path;
use League\Uri\Port;
use League\Uri\Query;
use League\Uri\Uri;
use League\Uri\User;
use League\Uri\UserInfo;
use League\Uri\Scheme;
use League\Uri\Schemes\Registry;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @group url
 */
class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $uri;

    public function setUp()
    {
        $uri = 'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3';
        $this->uri = Uri::createFromComponents(new Registry, Uri::parse($uri));
    }

    public function tearDown()
    {
        $this->uri = null;
    }

    public function testGetterAccess()
    {
        $this->assertSame($this->uri->getScheme(), $this->uri->scheme->__toString());
        $this->assertSame($this->uri->getUserInfo(), $this->uri->userInfo->__toString());
        $this->assertSame($this->uri->getHost(), $this->uri->host->__toString());
        $this->assertSame($this->uri->getPort(), $this->uri->port->toInt());
        $this->assertSame($this->uri->getPath(), $this->uri->path->__toString());
        $this->assertSame($this->uri->getQuery(), $this->uri->query->__toString());
        $this->assertSame($this->uri->getFragment(), $this->uri->fragment->__toString());
    }

    public function testImmutabilityAccess()
    {
        $this->assertSame($this->uri, $this->uri->withScheme('http'));
        $this->assertSame($this->uri, $this->uri->withUserInfo('login', 'pass'));
        $this->assertSame($this->uri, $this->uri->withHost('secure.example.com'));
        $this->assertSame($this->uri, $this->uri->withPort(443));
        $this->assertSame($this->uri, $this->uri->withPath('/test/query.php'));
        $this->assertSame($this->uri, $this->uri->withQuery('kingkong=toto'));
        $this->assertSame($this->uri, $this->uri->withFragment('doc3'));
    }

    public function testImmutabilityAccess2()
    {
        $this->assertNotEquals($this->uri, $this->uri->withScheme('ftp'));
        $this->assertNotEquals($this->uri, $this->uri->withUserInfo('login', null));
        $this->assertNotEquals($this->uri, $this->uri->withHost('shop.example.com'));
        $this->assertNotEquals($this->uri, $this->uri->withPort(81));
        $this->assertNotEquals($this->uri, $this->uri->withPath('/test/file.php'));
        $this->assertNotEquals($this->uri, $this->uri->withQuery('kingkong=tata'));
        $this->assertNotEquals($this->uri, $this->uri->withFragment('doc2'));
    }

    public function testGetAuthority()
    {
        $this->assertSame('login:pass@secure.example.com:443', $this->uri->getAuthority());
    }

    public function testGetUserInfo()
    {
        $this->assertSame('login:pass', $this->uri->getUserInfo());
    }

    public function testAutomaticUrlNormalization()
    {

        $url = Uri::createFromComponents(
            new Registry(),
            Uri::parse('HtTpS://MaStEr.eXaMpLe.CoM:443/%7ejohndoe/%a1/index.php?foo.bar=value#fragment')
        );

        $this->assertSame(
            'https://master.example.com/~johndoe/%A1/index.php?foo.bar=value#fragment',
            (string) $url
        );
    }

    /**
     * @param $url
     * @param $port
     * @dataProvider portProvider
     */
    public function testPort($url, $port)
    {
        $this->assertSame($port, Uri::createFromComponents(new Registry(), Uri::parse($url))->getPort());
    }

    public function portProvider()
    {
        return [
            ['http://www.example.com:443/', 443],
            ['http://www.example.com:80/', 80],
            ['http://www.example.com', null],
            ['//www.example.com:80/', 80],
        ];
    }

    /**
     * @param $url
     * @param $expected
     * @dataProvider toArrayProvider
     */
    public function testToArray($url, $expected)
    {
        $this->assertSame($expected, Uri::createFromComponents(new Registry(), Uri::parse($url))->toArray());
    }

    public function toArrayProvider()
    {
        return [
            'simple' => [
                'http://toto.com:443/toto.php',
                [
                    'scheme' => 'http',
                    'user' => null,
                    'pass' => null,
                    'host' => 'toto.com',
                    'port' => 443,
                    'path' => '/toto.php',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'ipv6 host' => [
                'https://[::1]:443/toto.php',
                [
                    'scheme' => 'https',
                    'user' => null,
                    'pass' => null,
                    'host' => '[::1]',
                    'port' => null,
                    'path' => '/toto.php',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'missing host' => [
                '/toto.php',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => '/toto.php',
                    'query' => null,
                    'fragment' => null,
                ],
            ],
            'relative path' => [
                'toto.php#fragment',
                [
                    'scheme' => null,
                    'user' => null,
                    'pass' => null,
                    'host' => null,
                    'port' => null,
                    'path' => 'toto.php',
                    'query' => null,
                    'fragment' => 'fragment',
                ],
            ],
        ];
    }

    /**
     * @param $url
     * @param $expected
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($url, $expected)
    {
        $this->assertSame($expected, $url->isEmpty());
    }

    public function isEmptyProvider()
    {
        return [
            'normal URL' => [Uri::createFromComponents(new Registry(), Uri::parse('http://a/b/c')), false],
            'incomplete authority' => [new Uri(
                new Registry(),
                new Scheme(),
                new UserInfo('foo', 'bar'),
                new Host(),
                new Port(80),
                new Path(),
                new Query(),
                new Fragment()
            ), true],
            'empty URL components' => [new Uri(
                new Registry(),
                new Scheme(),
                new UserInfo(),
                new Host(),
                new Port(),
                new Path(),
                new Query(),
                new Fragment()
            ), true],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithSchemeFailedWithUnsupportedScheme()
    {
        Uri::createFromComponents(new Registry(), Uri::parse('http://example.com'))->withScheme('telnet');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithRegistryFailedWithUnsupportedScheme()
    {
        Uri::createFromComponents(new Registry(), Uri::parse('http://example.com'))
            ->withSchemeRegistry(new Registry(['file' => null]));
    }

    public function testWithRegistryUpdateWithTheSameData()
    {
        $url = Uri::createFromComponents(new Registry(), Uri::parse('http://example.com'));
        $this->assertSame($url, $url->withSchemeRegistry(new Registry()));
    }

    public function testWithRegistry()
    {
        $url     = Uri::createFromComponents(new Registry(), Uri::parse('http://example.com'));
        $alt_url = $url->withSchemeRegistry((new Registry())->merge(['telnet' => 23]));
        $this->assertNotEquals($url, $alt_url);
        $this->assertTrue($url->sameValueAs($alt_url));
    }

    /**
     * @dataProvider sameValueAsPsr7InterfaceProvider
     */
    public function testSameValueAs($league, $psr7, $expected)
    {
        $mock = $this->getMock('Psr\Http\Message\UriInterface');
        $mock->method('__toString')->willReturn($psr7);

        $url = Uri::createFromComponents(new Registry(), Uri::parse($league));
        $this->assertSame($expected, $url->sameValueAs($mock));
    }

    public function sameValueAsPsr7InterfaceProvider()
    {
        return [
            ['http://example.com', 'yolo://example.com', false],
            ['http://example.com', 'http://example.com', true],
            ['//example.com', '//ExamPle.Com', true],
            ['http://مثال.إختبار', 'http://xn--mgbh0fb.xn--kgbechtv', true],
            ['http://example.com', 'http:///example.com', false],
            ['http:example.com', 'http:///example.com', false],
            ['http:/example.com', 'http:///example.com', false],
            ['http://example.org/~foo/', 'HTTP://example.ORG/~foo/', true],
            ['http://example.org/~foo/', 'http://example.org:80/~foo/', true],
            ['http://example.org/~foo/', 'http://example.org/%7Efoo/', true],
            ['http://example.org/~foo/', 'http://example.org/%7efoo/', true],
            ['http://example.org/~foo/', 'http://example.ORG/bar/./../~foo/', true],
        ];
    }

    /**
     * @param $url
     * @param $expected
     * @dataProvider pathFormattingProvider
     */
    public function testPathFormatting($url, $expected)
    {
        $this->assertSame($expected, $url->__toString());
    }

    public function pathFormattingProvider()
    {
        return [
            [new Uri(
                new Registry(),
                new Scheme('http'),
                new UserInfo(),
                new Host('ExAmPLe.cOm'),
                new Port(),
                new Path('path/to/the/sky'),
                new Query(),
                new Fragment()
            ), 'http://example.com/path/to/the/sky'],
            [new Uri(
                new Registry(),
                new Scheme('http'),
                new UserInfo(),
                new Host(),
                new Port(),
                new Path('///path/to/the/sky'),
                new Query(),
                new Fragment()
            ), 'http:/path/to/the/sky'],
        ];
    }

    /**
     * @dataProvider hasStandardPortProvider
     */
    public function testHasStandardPort($url, $expected)
    {
        $uri = Uri::createFromComponents(new Registry(), Uri::parse($url));
        $this->assertSame($expected, $uri->hasStandardPort());
    }

    public function hasStandardPortProvider()
    {
        return [
            ['http://example.com:81/', false],
            ['http://example.com:80/', true],
            ['http://example.com/', true],
        ];
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testResolve($url, $relative, $expected)
    {
        $url      = Uri::createFromComponents(new Registry(), Uri::parse($url));
        $relative = Uri::createFromComponents((new Registry())->merge(['mailto' => null]), Uri::parse($relative));

        $this->assertSame($expected, (string) $url->resolve($relative));
    }

    public function resolveProvider()
    {
        $base_url = "http://a/b/c/d;p?q";

        return [
          'opaque URI' =>              [$base_url, "mailto:email@example.com", "mailto:email@example.com"],
          'baseurl' =>                 [$base_url, "",               $base_url],
          'scheme' =>                  [$base_url, "ftp://d/e/f",    "ftp://d/e/f"],
          'path 1' =>                  [$base_url, "g",              "http://a/b/c/g"],
          'path 2' =>                  [$base_url, "./g",            "http://a/b/c/g"],
          'path 3' =>                  [$base_url, "g/",             "http://a/b/c/g/"],
          'path 4' =>                  [$base_url, "/g",             "http://a/g"],
          'authority' =>               [$base_url, "//g",            "http://g"],
          'query' =>                   [$base_url, "?y",             "http://a/b/c/d;p?y"],
          'path + query' =>            [$base_url, "g?y",            "http://a/b/c/g?y"],
          'fragment' =>                [$base_url, "#s",             "http://a/b/c/d;p?q#s"],
          'path + fragment' =>         [$base_url, "g#s",            "http://a/b/c/g#s"],
          'path + query + fragment' => [$base_url, "g?y#s",          "http://a/b/c/g?y#s"],
          'single dot 1'=>             [$base_url, ".",              "http://a/b/c/"],
          'single dot 2' =>            [$base_url, "./",             "http://a/b/c/"],
          'single dot 3' =>            [$base_url, "./g/.",          "http://a/b/c/g/"],
          'single dot 4' =>            [$base_url, "g/./h",          "http://a/b/c/g/h"],
          'double dot 1' =>            [$base_url, "..",             "http://a/b/"],
          'double dot 2' =>            [$base_url, "../",            "http://a/b/"],
          'double dot 3' =>            [$base_url, "../g",           "http://a/b/g"],
          'double dot 4' =>            [$base_url, "../..",          "http://a/"],
          'double dot 5' =>            [$base_url, "../../",         "http://a/"],
          'double dot 6' =>            [$base_url, "../../g",        "http://a/g"],
          'double dot 7' =>            [$base_url, "../../../g",     "http://a/g"],
          'double dot 8' =>            [$base_url, "../../../../g",  "http://a/g"],
          'double dot 9' =>            [$base_url, "g/../h" ,        "http://a/b/c/h"],
          'mulitple slashes' =>        [$base_url, "foo////g",       "http://a/b/c/foo////g"],
          'complex path 1' =>          [$base_url, ";x",             "http://a/b/c/;x"],
          'complex path 2' =>          [$base_url, "g;x",            "http://a/b/c/g;x"],
          'complex path 3' =>          [$base_url, "g;x?y#s",        "http://a/b/c/g;x?y#s"],
          'complex path 4' =>          [$base_url, "g;x=1/./y",      "http://a/b/c/g;x=1/y"],
          'complex path 5' =>          [$base_url, "g;x=1/../y",     "http://a/b/c/y"],
          'origin url without path' => ["http://h:b@a", "b/../y",         "http://h:b@a/y"],
          '2 relative paths 1'      => ["a/b",          "../..",          "/"],
          '2 relative paths 2'      => ["a/b",          "./.",            "a/"],
          '2 relative paths 3'      => ["a/b",          "../c",           "c"],
          '2 relative paths 4'      => ["a/b",          "c/..",           "a/"],
          '2 relative paths 5'      => ["a/b",          "c/.",            "a/c/"],
        ];
    }

    public function testResolveWithDifferentSchemeRegistry()
    {
        $schemeRegistry = new Registry(['telnet' => 23]);
        $telnet = Uri::createFromComponents($schemeRegistry, Uri::parse('telnet://example.com/toto'));
        $telnet = $telnet->withScheme('');
        $http   = Uri::createFromComponents(new Registry(), Uri::parse('http://example.com/tata/../toto.csv'));
        $url    = $http->resolve($telnet);

        $this->assertNotEquals($http->schemeRegistry, $telnet->schemeRegistry);
        $this->assertNotEquals($url->schemeRegistry, $telnet->schemeRegistry);
        $this->assertNotEquals($url->schemeRegistry, $http->schemeRegistry);
    }

    /**
     * @dataProvider invalidURL
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromInvalidUrlKO($input)
    {
        Uri::parse($input);
    }

    public function invalidURL()
    {
        return [
            ["http://user@:80"],
            ["//user@:80"],
            ["http:///example.com"],
        ];
    }
}

<?php

namespace League\Url\Test;

use League\Url\Fragment;
use League\Url\Host;
use League\Url\Pass;
use League\Url\Path;
use League\Url\Port;
use League\Url\Query;
use League\Url\Url;
use League\Url\User;
use League\Url\UserInfo;
use League\Url\Scheme;
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
    private $url;

    const BASE_URL = "http://a/b/c/d;p?q";

    public function setUp()
    {
        $this->url = Url::createFromUrl(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    public function tearDown()
    {
        $this->url = null;
    }

    public function testGetterAccess()
    {
        $this->assertSame($this->url->getScheme(), $this->url->scheme->__toString());
        $this->assertSame($this->url->getUserInfo(), $this->url->userInfo->__toString());
        $this->assertSame($this->url->getHost(), $this->url->host->__toString());
        $this->assertSame($this->url->getPort(), $this->url->port->toInt());
        $this->assertSame($this->url->getPath(), $this->url->path->__toString());
        $this->assertSame($this->url->getQuery(), $this->url->query->__toString());
        $this->assertSame($this->url->getFragment(), $this->url->fragment->__toString());
    }

    public function testImmutabilityAccess()
    {
        $this->assertSame($this->url, $this->url->withScheme('http'));
        $this->assertSame($this->url, $this->url->withUserInfo('login', 'pass'));
        $this->assertSame($this->url, $this->url->withHost('secure.example.com'));
        $this->assertSame($this->url, $this->url->withPort(443));
        $this->assertSame($this->url, $this->url->withPath('/test/query.php'));
        $this->assertSame($this->url, $this->url->withQuery('kingkong=toto'));
        $this->assertSame($this->url, $this->url->withFragment('doc3'));
    }

    public function testImmutabilityAccess2()
    {
        $this->assertNotEquals($this->url, $this->url->withScheme('ftp'));
        $this->assertNotEquals($this->url, $this->url->withUserInfo('login', null));
        $this->assertNotEquals($this->url, $this->url->withHost('shop.example.com'));
        $this->assertNotEquals($this->url, $this->url->withPort(81));
        $this->assertNotEquals($this->url, $this->url->withPath('/test/file.php'));
        $this->assertNotEquals($this->url, $this->url->withQuery('kingkong=tata'));
        $this->assertNotEquals($this->url, $this->url->withFragment('doc2'));
    }

    public function testGetAuthority()
    {
        $this->assertSame('login:pass@secure.example.com:443', $this->url->getAuthority());
    }

    public function testGetUserInfo()
    {
        $this->assertSame('login:pass', $this->url->getUserInfo());
    }

    public function testAutomaticUrlNormalization()
    {
        $url = Url::createFromUrl(
            'HtTpS://MaStEr.eXaMpLe.CoM:443/%7ejohndoe/%a1/index.php?foo.bar=value#fragment'
        );

        $this->assertSame(
            'https://master.example.com/~johndoe/%A1/index.php?foo.bar=value#fragment',
            (string) $url
        );
    }

    public function testComparingWithUriInterfaceObject()
    {
        $mock = $this->getMock('Psr\Http\Message\UriInterface');
        $mock->method('__toString')->willReturn('http://gwóźdź.pl/toto/path');
        $url = Url::createFromUrl('http://xn--gwd-hna98db.pl/toto/path');

        $this->assertTrue($url->sameValueAs($mock));
    }

    /**
     * @param $url
     * @param $port
     * @dataProvider portProvider
     */
    public function testPort($url, $port)
    {
        $this->assertSame($port, Url::createFromUrl($url)->getPort());
    }

    public function portProvider()
    {
        return [
            ['http://www.example.com:443/', 443],
            ['http://www.example.com:80/', null],
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
        $this->assertSame($expected, Url::createFromUrl($url)->toArray());
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

    public function testEmptyConstructor()
    {
        $url = new Url(
            new Scheme(),
            new UserInfo(),
            new Host(),
            new Port(),
            new Path(),
            new Query(),
            new Fragment()
        );

        $this->assertEmpty($url->__toString());
    }

    public function testSameValueAs()
    {
        $url1 = new Url(
            new Scheme(),
            new UserInfo(),
            new Host('example.com'),
            new Port(),
            new Path(),
            new Query(),
            new Fragment()
        );

        $url2 = new Url(
            new Scheme(),
            new UserInfo(),
            new Host('ExAmPLe.cOm'),
            new Port(),
            new Path(),
            new Query(),
            new Fragment()
        );

        $this->assertTrue($url1->sameValueAs($url2));
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
            [new Url(
                new Scheme('http'),
                new UserInfo(),
                new Host('ExAmPLe.cOm'),
                new Port(),
                new Path('path/to/the/sky'),
                new Query(),
                new Fragment()
            ), 'http://example.com/path/to/the/sky'],
        ];
    }

    public function testHasStandardPort()
    {
        $this->assertFalse(Url::createFromUrl('http://example.com:81/')->hasStandardPort());
        $this->assertTrue(Url::createFromUrl('http://example.com:80/')->hasStandardPort());
        $this->assertTrue(Url::createFromUrl('http://example.com/')->hasStandardPort());
    }

    /**
     * @param $relative
     * @param $expected
     *
     * @dataProvider resolveProvider
     */
    public function testResolve($url, $relative, $expected)
    {
        $this->assertSame($expected, Url::createFromUrl($url)->resolve($relative)->__toString());
    }

    public function resolveProvider()
    {
        return [
          'baseurl' =>                 [self::BASE_URL, "",               self::BASE_URL],
          'scheme' =>                  [self::BASE_URL, "ftp://d/e/f",    "ftp://d/e/f"],
          'path 1' =>                  [self::BASE_URL, "g",              "http://a/b/c/g"],
          'path 2' =>                  [self::BASE_URL, "./g",            "http://a/b/c/g"],
          'path 3' =>                  [self::BASE_URL, "g/",             "http://a/b/c/g/"],
          'path 4' =>                  [self::BASE_URL, "/g",             "http://a/g"],
          'authority' =>               [self::BASE_URL, "//g",            "http://g"],
          'query' =>                   [self::BASE_URL, "?y",             "http://a/b/c/d;p?y"],
          'path + query' =>            [self::BASE_URL, "g?y",            "http://a/b/c/g?y"],
          'fragment' =>                [self::BASE_URL, "#s",             "http://a/b/c/d;p?q#s"],
          'path + fragment' =>         [self::BASE_URL, "g#s",            "http://a/b/c/g#s"],
          'path + query + fragment' => [self::BASE_URL, "g?y#s",          "http://a/b/c/g?y#s"],
          'single dot 1'=>             [self::BASE_URL, ".",              "http://a/b/c/"],
          'single dot 2' =>            [self::BASE_URL, "./",             "http://a/b/c/"],
          'single dot 3' =>            [self::BASE_URL, "./g/.",          "http://a/b/c/g/"],
          'single dot 4' =>            [self::BASE_URL, "g/./h",          "http://a/b/c/g/h"],
          'double dot 1' =>            [self::BASE_URL, "..",             "http://a/b/"],
          'double dot 2' =>            [self::BASE_URL, "../",            "http://a/b/"],
          'double dot 3' =>            [self::BASE_URL, "../g",           "http://a/b/g"],
          'double dot 4' =>            [self::BASE_URL, "../..",          "http://a/"],
          'double dot 5' =>            [self::BASE_URL, "../../",         "http://a/"],
          'double dot 6' =>            [self::BASE_URL, "../../g",        "http://a/g"],
          'double dot 7' =>            [self::BASE_URL, "../../../g",     "http://a/g"],
          'double dot 8' =>            [self::BASE_URL, "../../../../g",  "http://a/g"],
          'double dot 9' =>            [self::BASE_URL, "g/../h" ,        "http://a/b/c/h"],
          'mulitple slashes' =>        [self::BASE_URL, "foo////g",       "http://a/b/c/foo////g"],
          'complex path 1' =>          [self::BASE_URL, ";x",             "http://a/b/c/;x"],
          'complex path 2' =>          [self::BASE_URL, "g;x",            "http://a/b/c/g;x"],
          'complex path 3' =>          [self::BASE_URL, "g;x?y#s",        "http://a/b/c/g;x?y#s"],
          'complex path 4' =>          [self::BASE_URL, "g;x=1/./y",      "http://a/b/c/g;x=1/y"],
          'complex path 5' =>          [self::BASE_URL, "g;x=1/../y",     "http://a/b/c/y"],
          'origin url without path' => ["http://h:b@a",     "b/../y",         "http://h:b@a/y"],
          '2 relative paths 1'      => ["a/b",          "../..",          "/"],
          '2 relative paths 2'      => ["a/b",          "./.",            "a/"],
          '2 relative paths 3'      => ["a/b",          "../c",           "c"],
          '2 relative paths 4'      => ["a/b",          "c/..",           "a/"],
          '2 relative paths 5'      => ["a/b",          "c/.",            "a/c/"],
        ];
    }
}

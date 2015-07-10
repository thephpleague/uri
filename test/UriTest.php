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

    public function setUp()
    {
        $this->url = Uri::createFromString(
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
        $url = Uri::createFromString(
            'HtTpS://MaStEr.eXaMpLe.CoM:443/%7ejohndoe/%a1/index.php?foo.bar=value#fragment'
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
        $this->assertSame($port, Uri::createFromString($url)->getPort());
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
        $this->assertSame($expected, Uri::createFromString($url)->toArray());
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
            'normal URL' => [Uri::createFromString('http://a/b/c'), false],
            'incomplete authority' => [new Uri(
                new Scheme(),
                new UserInfo('foo', 'bar'),
                new Host(),
                new Port(80),
                new Path(),
                new Query(),
                new Fragment(),
                new Scheme\Registry()
            ), true],
            'empty URL components' => [new Uri(
                new Scheme(),
                new UserInfo(),
                new Host(),
                new Port(),
                new Path(),
                new Query(),
                new Fragment(),
                new Scheme\Registry()
            ), true],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithSchemeFailedWithUnsupportedScheme()
    {
        Uri::createFromString('http://example.com')->withScheme('telnet');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithRegistryFailedWithUnsupportedScheme()
    {
        $registry = new Scheme\Registry(['file' => null]);
        Uri::createFromString('http://example.com')->withSchemeRegistry($registry);
    }

    public function testWithRegistryUpdateWithTheSameData()
    {
        $registry = new Scheme\Registry();
        $url      = Uri::createFromString('http://example.com');
        $this->assertSame($url, $url->withSchemeRegistry($registry));
    }

    public function testWithRegistry()
    {
        $registry = (new Scheme\Registry())->merge(['telnet' => 23]);
        $url      = Uri::createFromString('http://example.com');
        $alt_url  = $url->withSchemeRegistry($registry);
        $this->assertNotEquals($url, $alt_url);
        $this->assertTrue($url->sameValueAs($alt_url));
    }

    /**
     * @param  $url1
     * @param  $url2
     * @dataProvider sameValueAsProvider
     */
    public function testSameValueAs($url1, $url2)
    {
        $this->assertTrue($url1->sameValueAs($url2));
    }

    public function sameValueAsProvider()
    {
        $mock = $this->getMock('Psr\Http\Message\UriInterface');
        $mock->method('__toString')->willReturn('http://xn--gwd-hna98db.pl/toto/path');
        $url = Uri::createFromString('http://xn--gwd-hna98db.pl/toto/path');

        return [
            [Uri::createFromString('//example.com'), Uri::createFromString('//ExamPle.Com')],
            [Uri::createFromString('http://مثال.إختبار'), Uri::createFromString('http://مثال.إختبار')],
            [$url, $mock],
        ];
    }

    /**
     * @dataProvider sameValueAsPsr7InterfaceProvider
     */
    public function testSameValueAsFailed($league, $psr7, $expected)
    {
        $mock = $this->getMock('Psr\Http\Message\UriInterface');
        $mock->method('__toString')->willReturn($psr7);
        $this->assertSame($expected, Uri::createFromString($league)->sameValueAs($mock));
    }

    public function sameValueAsPsr7InterfaceProvider()
    {
        return [
            ['http://example.com', 'yolo://example.com', false],
            ['http://example.com', 'http://example.com', true],
            ['//example.com', '//ExamPle.Com', true],
            ['http://مثال.إختبار', 'http://xn--mgbh0fb.xn--kgbechtv', true],
            ['http://example.com', 'http:///example.com', false],
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
                new Scheme('http'),
                new UserInfo(),
                new Host('ExAmPLe.cOm'),
                new Port(),
                new Path('path/to/the/sky'),
                new Query(),
                new Fragment(),
                new Scheme\Registry()
            ), 'http://example.com/path/to/the/sky'],
            [new Uri(
                new Scheme('http'),
                new UserInfo(),
                new Host(),
                new Port(),
                new Path('///path/to/the/sky'),
                new Query(),
                new Fragment(),
                new Scheme\Registry()
            ), 'http:/path/to/the/sky'],
        ];
    }

    public function testHasStandardPort()
    {
        $this->assertFalse(Uri::createFromString('http://example.com:81/')->hasStandardPort());
        $this->assertTrue(Uri::createFromString('http://example.com:80/')->hasStandardPort());
        $this->assertTrue(Uri::createFromString('http://example.com/')->hasStandardPort());
    }

    /**
     * @param $relative
     * @param $expected
     *
     * @dataProvider resolveProvider
     */
    public function testResolve($url, $relative, $expected)
    {
        $this->assertSame($expected, (string) Uri::createFromString($url)->resolve(Uri::createFromString($relative)));
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
        $schemeRegistry = new Scheme\Registry(['telnet' => 23]);
        $telnet = Uri::createFromString('telnet://example.com/toto', $schemeRegistry)->withScheme('');
        $http   = Uri::createFromString('http://example.com/tata/../toto.csv', new Scheme\Registry());
        $url    = $http->resolve($telnet);

        $this->assertNotEquals($http->schemeRegistry, $telnet->schemeRegistry);
        $this->assertNotEquals($url->schemeRegistry, $telnet->schemeRegistry);
        $this->assertNotEquals($url->schemeRegistry, $http->schemeRegistry);
    }
}

<?php

namespace League\Uri\test\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Components;
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group uri
 */
class HierarchicalUriTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $uri;

    public function setUp()
    {
        $this->uri = HttpUri::createFromString(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
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

    public function testKeepSameInstanceIfPropertyDoesNotChange()
    {
        $this->assertSame($this->uri, $this->uri->withScheme('http'));
        $this->assertSame($this->uri, $this->uri->withUserInfo('login', 'pass'));
        $this->assertSame($this->uri, $this->uri->withHost('secure.example.com'));
        $this->assertSame($this->uri, $this->uri->withPort(443));
        $this->assertSame($this->uri, $this->uri->withPath('/test/query.php'));
        $this->assertSame($this->uri, $this->uri->withQuery('kingkong=toto'));
        $this->assertSame($this->uri, $this->uri->withFragment('doc3'));
    }

    public function testCreateANewInstanceWhenPropertyChanges()
    {
        $this->assertNotEquals($this->uri, $this->uri->withScheme('https'));
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
        $raw = 'HtTpS://MaStEr.eXaMpLe.CoM:443/%7ejohndoe/%a1/in+dex.php?foo.bar=value#fragment';
        $normalized = 'https://master.example.com/~johndoe/%A1/in+dex.php?foo.bar=value#fragment';
        $this->assertSame($normalized, (string) HttpUri::createFromString($raw));
    }

    /**
     * @param $uri
     * @param $port
     * @dataProvider portProvider
     */
    public function testPort($uri, $port)
    {
        $this->assertSame($port, HttpUri::createFromString($uri)->getPort());
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
     * @param $uri
     * @param $expected
     * @dataProvider toArrayProvider
     */
    public function testToArray($uri, $expected)
    {
        $this->assertSame($expected, HttpUri::createFromString($uri)->toArray());
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
     * @param $uri
     * @param $expected
     * @dataProvider isEmptyProvider
     */
    public function testIsEmpty($uri, $expected)
    {
        $this->assertSame($expected, $uri->isEmpty());
    }

    public function isEmptyProvider()
    {
        return [
            'normal URI' => [HttpUri::createFromString('http://a/b/c'), false],
            'incomplete authority' => [new HttpUri(
                new Components\Scheme(),
                new Components\UserInfo('foo', 'bar'),
                new Components\Host(),
                new Components\Port(80),
                new Components\HierarchicalPath(),
                new Components\Query(),
                new Components\Fragment()
            ), true],
            'empty URI components' => [new HttpUri(
                new Components\Scheme(),
                new Components\UserInfo(),
                new Components\Host(),
                new Components\Port(),
                new Components\HierarchicalPath(),
                new Components\Query(),
                new Components\Fragment()
            ), true],
        ];
    }

    /**
     * @dataProvider sameValueAsPsr7InterfaceProvider
     * @param $league
     * @param $psr7
     * @param $expected
     */
    public function testSameValueAs($league, $psr7, $expected)
    {
        $mock = $this->getMock('Psr\Http\Message\UriInterface');
        $mock->method('__toString')->willReturn($psr7);

        $uri = HttpUri::createFromString($league);

        $this->assertSame($expected, $uri->sameValueAs($mock));
    }

    public function sameValueAsPsr7InterfaceProvider()
    {
        return [
            ['http://example.com', 'yolo://example.com', false],
            ['http://example.com', 'http://example.com', true],
            ['//example.com', '//ExamPle.Com', true],
            ['http://مثال.إختبار', 'http://xn--mgbh0fb.xn--kgbechtv', true],
            ['http://example.com', 'http:///example.com', false],
            ['http://example.com', 'http:example.com', false],
            ['http://example.com', 'http:/example.com', false],
            ['http://example.org/~foo/', 'HTTP://example.ORG/~foo/', true],
            ['http://example.org/~foo/', 'http://example.org:80/~foo/', true],
            ['http://example.org/~foo/', 'http://example.org/%7Efoo/', true],
            ['http://example.org/~foo/', 'http://example.org/%7efoo/', true],
            ['http://example.org/~foo/', 'http://example.ORG/bar/./../~foo/', true],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSameValueAsFailedWithUnknownType()
    {
        HttpUri::createFromString('http://example.com')->sameValueAs('http://example.com');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithSchemeFailedWithUnsupportedScheme()
    {
        HttpUri::createFromString('http://example.com')->withScheme('telnet');
    }

    /**
     * @param $uri
     * @param $expected
     * @dataProvider pathFormattingProvider
     */
    public function testPathFormatting($uri, $expected)
    {
        $this->assertSame($expected, $uri->__toString());
    }

    public function pathFormattingProvider()
    {
        return [
            [new HttpUri(
                new Components\Scheme('http'),
                new Components\UserInfo(),
                new Components\Host('ExAmPLe.cOm'),
                new Components\Port(),
                new Components\HierarchicalPath('path/to/the/sky'),
                new Components\Query(),
                new Components\Fragment()
            ), 'http://example.com/path/to/the/sky'],
            [new HttpUri(
                new Components\Scheme(''),
                new Components\UserInfo(),
                new Components\Host(),
                new Components\Port(),
                new Components\HierarchicalPath('///path/to/the/sky'),
                new Components\Query(),
                new Components\Fragment()
            ), '/path/to/the/sky'],
        ];
    }

    /**
     * @dataProvider hasStandardPortProvider
     * @param $uri
     * @param $expected
     */
    public function testHasStandardPort($uri, $expected)
    {
        $this->assertSame($expected, HttpUri::createFromString($uri)->hasStandardPort());
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
     * @dataProvider relativizeProvider
     * @param $base
     * @param $child
     * @param $expected
     */
    public function testRelativize($base, $child, $expected)
    {
        $baseUri  = HttpUri::createFromString($base);
        $childUri = HttpUri::createFromString($child);

        $this->assertSame($expected, (string) $baseUri->relativize($childUri));
    }

    public function testRelativizeWithNonHierarchicalUri()
    {
        $httpUri = HttpUri::createFromString('http://www.example.com/path');
        $dataUri = DataUri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21');

        $this->assertSame($dataUri, $httpUri->relativize($dataUri));
    }


    /**
     * @dataProvider mixUriProvider
     * @param $input
     * @param $relative
     */
    public function testRelativizeUriObject($input, $relative)
    {
        $this->assertSame($relative, $input->relativize($relative));
    }

    public function mixUriProvider()
    {
        return [
            [
                FtpUri::createFromString('ftp://example.com/path/to/file'),
                HttpUri::createFromString('//a/b/c/d;p?q'),
            ],
            [
                FtpUri::createFromString('//example.com/path/to/file'),
                HttpUri::createFromString('./g'),
            ],
        ];
    }

    public function relativizeProvider()
    {
        return [
            ['http://www.example.com/foo/bar', 'http://toto.com', 'http://toto.com'],
            ['http://www.example.com/foo/bar', 'http://www.example.com:81/foo', 'http://www.example.com:81/foo'],
            ['http://www.example.com/toto/le/heros', 'http://www.example.com/bar', '../bar'],
            ['http://www.example.com/toto/le/heros/', 'http://www.example.com/bar', '../bar'],
            ['http://www.example.com/toto/le/../heros/', 'http://www.example.com/../bar', 'bar'],
            ['http://www.example.com/toto/le/heros/', 'http://www.example.com/bar?query=value', '../bar?query=value'],
        ];
    }

    /**
     * @dataProvider invalidURI
     * @expectedException InvalidArgumentException
     * @param $input
     */
    public function testCreateFromInvalidUrlKO($input)
    {
        HttpUri::createFromString($input);
    }

    public function invalidURI()
    {
        return [
            ['http://user@:80'],
            ['//user@:80'],
        ];
    }

    /**
     * @expectedException RuntimeException
     */
    public function testModificationFailed()
    {
        HttpUri::createFromString('http://example.com/path')
            ->withScheme('')
            ->withHost('')
            ->withPath('data:go');
    }
}

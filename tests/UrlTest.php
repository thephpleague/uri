<?php

namespace League\Url\Test;

use League\Url\Url;
use PHPUnit_Framework_TestCase;

/**
 * @group immutable
 */
class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $url;

    public function setUp()
    {
        $this->url = Url::createFromUrl(
            'https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    public function tearDown()
    {
        $this->url = null;
    }

    public function testGetterAccess()
    {
        $this->assertInstanceof('League\Url\Scheme', $this->url->getScheme());
        $this->assertInstanceof('League\Url\User', $this->url->getUser());
        $this->assertInstanceof('League\Url\Pass', $this->url->getPass());
        $this->assertInstanceof('League\Url\Host', $this->url->getHost());
        $this->assertInstanceof('League\Url\Port', $this->url->getPort());
        $this->assertInstanceof('League\Url\Path', $this->url->getPath());
        $this->assertInstanceof('League\Url\Query', $this->url->getQuery());
        $this->assertInstanceof('League\Url\Fragment', $this->url->getFragment());
    }

    public function testSetterAccess()
    {
        $this->assertEquals($this->url, $this->url->withScheme('https'));
        $this->assertEquals($this->url, $this->url->withUser('login'));
        $this->assertEquals($this->url, $this->url->withPass('pass'));
        $this->assertEquals($this->url, $this->url->withHost('secure.example.com'));
        $this->assertEquals($this->url, $this->url->withPort(443));
        $this->assertEquals($this->url, $this->url->withPath('/test/query.php'));
        $this->assertEquals($this->url, $this->url->withQuery('?kingkong=toto'));
        $this->assertEquals($this->url, $this->url->withFragment('doc3'));
    }

    public function testCreateFromServer()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'example.com',
        ];
        $url = Url::createFromServer($server);
        $this->assertInstanceof('\League\Url\Url', $url);
        $this->assertSame('https://example.com:23/', $url->__toString());

        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        ];

        $url = Url::createFromServer($server);
        $this->assertInstanceof('\League\Url\Url', $url);
        $this->assertSame('https://127.0.0.1:23/', $url->__toString());
    }

    public function testCreateFromServerWithHttpHostAndPort()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'localhost:23',
        ];
        $url = Url::createFromServer($server);
        $this->assertInstanceof('\League\Url\Url', $url);
        $this->assertSame('https://localhost:23/', $url->__toString());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFailCreateFromServerWithoutHost()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        ];

        Url::createFromServer($server);
    }

    public function testCreateFromServerWithoutRequestUri()
    {
        $server = [
            'PHP_SELF' => '/toto?foo=bar',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        ];
        $url = Url::createFromServer($server);
        $this->assertSame('https://127.0.0.1:23/toto?foo=bar', (string) $url);

        $server = [
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        ];
        $url = Url::createFromServer($server);

        $this->assertSame('https://127.0.0.1:23/', (string) $url);
    }

    public function testConstructor()
    {
        $expected = 'http://example.com:80/foo/bar?foo=bar#content';
        $this->assertSame($expected, (string) Url::createFromUrl($expected));
        $this->assertSame('//example.com/', (string) Url::createFromUrl('example.com'));
        $this->assertSame('//example.com/', (string) Url::createFromUrl('//example.com'));
        $this->assertSame(
            '//login@example.com/',
            (string) Url::createFromUrl('login@example.com/')
        );
        $this->assertSame(
            '//login:pass@example.com/',
            (string) Url::createFromUrl('login:pass@example.com/')
        );
        $this->assertSame(
            'http://login:pass@example.com/',
            (string) Url::createFromUrl('http://login:pass@example.com/')
        );
    }

    public function testCreateEmptyUrl()
    {
        $this->assertSame('', (string) Url::createFromUrl(""));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromInvalidUrl()
    {
        Url::createFromUrl('/path/to/url.html');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromInvalidUrlKO()
    {
        Url::createFromUrl("http://user@:80");
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromUrlBadName()
    {
        Url::createFromUrl('http:/google.com');
    }

    public function testCreateFromSingleLabelHost()
    {
        $url = Url::createFromUrl('sdfsdfqsdfsdf');
        $this->assertSame('//sdfsdfqsdfsdf/', $url->__toString());
    }

    public function testStringRepresentation()
    {
        $url = Url::createFromUrl(
            'https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
        $this->assertSame('https://login:pass@secure.example.com:443', $url->getBaseUrl());
        $this->assertSame('login:pass@secure.example.com:443', $url->getAuthority());
        $this->assertSame('login:pass@', $url->getUserInfo());
    }

    public function testAutomaticUrlNormalization()
    {
        $url = Url::createFromUrl(
            'HtTpS://MaStEr.eXaMpLe.CoM:80/%7ejohndoe/%a1/index.php?foo.bar=value#fragment'
        );
        $this->assertSame(
            'https://master.example.com:80/~johndoe/%A1/index.php?foo.bar=value#fragment',
            (string) $url
        );
    }

    public function testToArray()
    {
        $url = Url::createFromUrl('https://toto.com:443/toto.php');
        $this->assertSame([
            'scheme' => 'https',
            'user' => null,
            'pass' => null,
            'host' => 'toto.com',
            'port' => 443,
            'path' => 'toto.php',
            'query' => null,
            'fragment' => null,
        ], $url->toArray());
    }

    public function testSameValueAs()
    {
        $url1 = Url::createFromUrl('example.com');
        $url2 = Url::createFromUrl('//example.com');
        $url3 = Url::createFromUrl('//example.com?foo=toto+le+heros');
        $this->assertTrue($url1->sameValueAs($url2));
        $this->assertFalse($url3->sameValueAs($url2));
    }
}

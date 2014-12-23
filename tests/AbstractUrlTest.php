<?php

namespace League\Url\Test;

use League\Url\Url;
use League\Url\UrlImmutable;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * @group factory
 */
class AbstractUrlTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromServer()
    {
        $server = array(
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'example.com',
        );
        $url = UrlImmutable::createFromServer($server);
        $this->assertInstanceof('\League\Url\UrlImmutable', $url);
        $this->assertSame('https://example.com:23/', $url->__toString());

        $server = array(
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );

        $url = Url::createFromServer($server);
        $this->assertInstanceof('\League\Url\Url', $url);
        $this->assertSame('https://127.0.0.1:23/', $url->__toString());
    }

    public function testCreateFromServerWithHttpHostAndPort()
    {
        $server = array(
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'localhost:23',
        );
        $url = UrlImmutable::createFromServer($server);
        $this->assertInstanceof('\League\Url\UrlImmutable', $url);
        $this->assertSame('https://localhost:23/', $url->__toString());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFailCreateFromServerWithoutHost()
    {
        $server = array(
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );

        Url::createFromServer($server);
    }

    public function testCreateFromServerWithoutRequestUri()
    {
        $server = array(
            'PHP_SELF' => '/toto?foo=bar',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );
        $url = Url::createFromServer($server);
        $this->assertSame('https://127.0.0.1:23/toto?foo=bar', (string) $url);

        $server = array(
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );
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

    public function testRelativeUrlRepresentation()
    {
        $url = Url::createFromUrl(
            'https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );

        $url_internal_link = Url::createFromUrl(
            'https://login:pass@secure.example.com:443/toto.php'
        );

        $url_external_link = Url::createFromUrl(
            'https://toto.com:443/toto.php'
        );

        $url_similar = Url::createFromUrl(
            'https://login:pass@secure.example.com:443/lol/query.php'
        );

        $url_same_path = Url::createFromUrl(
            'https://login:pass@secure.example.com:443/test/query.php?godzilla=monster'
        );

        $this->assertSame('/test/query.php?kingkong=toto#doc3', $url->getUrl());
        $this->assertSame('../test/query.php?kingkong=toto#doc3', $url->getUrl($url_internal_link));
        $this->assertSame('../../toto.php', $url_internal_link->getUrl($url));
        $this->assertSame($url->__toString(), $url->getUrl($url_external_link));
        $this->assertSame('../../test/query.php?kingkong=toto#doc3', $url->getUrl($url_similar));
        $this->assertSame('?kingkong=toto#doc3', $url->getUrl($url_same_path));
    }

    public function testToArray()
    {
        $url1 = Url::createFromUrl('https://toto.com:443/toto.php');
        $url2 = UrlImmutable::createFromUrl('https://toto.com:443/toto.php');
        $this->assertSame($url1->toArray(), $url2->toArray());
    }

    public function testSameValueAs()
    {
        $url1 = Url::createFromUrl('example.com');
        $url2 = UrlImmutable::createFromUrl('//example.com');
        $url3 = UrlImmutable::createFromUrl('//example.com?foo=toto+le+heros');
        $this->assertTrue($url1->sameValueAs($url2));
        $this->assertFalse($url3->sameValueAs($url2));
    }
}

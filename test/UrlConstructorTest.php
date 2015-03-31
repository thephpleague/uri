<?php

namespace League\Url\Test;

use League\Url\Url;
use League\Url\Scheme;
use League\Url\User;
use League\Url\Pass;
use League\Url\Host;
use League\Url\Port;
use League\Url\Path;
use League\Url\Query;
use League\Url\Fragment;
use PHPUnit_Framework_TestCase;

/**
 * @group urlconstructor
 */
class UrlConstructorTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromServerWithHttpHost()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'example.com',
        ];
        $this->assertSame('https://example.com:23', Url::createFromServer($server)->__toString());
    }

    public function testCreateFromServerWithServerAddr()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
        ];

        $this->assertSame('https://127.0.0.1:23', Url::createFromServer($server)->__toString());
    }

    public function testCreateFromServerWithHttpHostAndPort()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'localhost:23',
        ];

        $this->assertSame('https://localhost:23', Url::createFromServer($server)->__toString());
    }


    public function testCreateFromServerWithXforwardHeader()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'localhost:23',
        ];

        $this->assertSame('https://localhost:23', Url::createFromServer($server)->__toString());
    }

    public function testCreateFromServerWithUserInfo()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW' => 'bar',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'localhost:23',
        ];

        $this->assertSame('https://foo:bar@localhost:23', Url::createFromServer($server)->__toString());
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

        Url::createFromServer($server);
    }

    public function testCreateFromServerWithoutRequestUri()
    {
        $server = [
            'PHP_SELF' => '/toto',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
            'QUERY_STRING' => 'foo=bar',
        ];

        $this->assertSame('https://127.0.0.1:23/toto?foo=bar', (string) Url::createFromServer($server));
    }

    public function testCreateFromServerWithoutRequestUriAndServerHost()
    {
        $server = [
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PORT' => 23,
        ];

        $this->assertSame('https://127.0.0.1:23', (string) Url::createFromServer($server));
    }

    public function testConstructor()
    {
        $this->assertSame(
            'http://example.com/foo/bar?foo=bar#content',
            (string) Url::createFromUrl('http://example.com:80/foo/bar?foo=bar#content')
        );
    }

    public function testConstructorWithoutSchemeButWithSchemeDelimiter()
    {
        $this->assertSame('//example.com', (string) Url::createFromUrl('//example.com'));
    }

    public function testConstructorWithUserInfoAndScheme()
    {
        $this->assertSame(
            'http://login:pass@example.com/',
            (string) Url::createFromUrl('http://login:pass@example.com/')
        );
    }

    public function testConstructorWithEmptyString()
    {
        $this->assertSame('', (string) Url::createFromUrl(""));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromInvalidUrlKO()
    {
        Url::createFromUrl("http://user@:80");
    }
}

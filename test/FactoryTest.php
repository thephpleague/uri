<?php

namespace League\Url\test;

use League\Url\Url;
use League\Url\UrlImmutable;
use PHPUnit_Framework_TestCase;
use StdClass;
use RuntimeException;

/**
 * @group factory
 */
class FactoryTest extends PHPUnit_Framework_TestCase
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

        Url::createFromServer($server, true);
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
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateFromUrlKO()
    {
        Url::createFromUrl(new StdClass);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromUrlBadName()
    {
        Url::createFromUrl('http:/google.com');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromUrlBadName2()
    {
        Url::createFromUrl('sdfsdfqsdfsdf');
    }
}

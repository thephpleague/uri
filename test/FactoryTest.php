<?php

namespace League\Url\test;

use League\Url\Factory;
use PHPUnit_Framework_TestCase;
use StdClass;

class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $url;

    private $url_factory;

    public function setUp()
    {

        $this->url_factory = new Factory;

        $this->url = $this->url_factory->createFromString(
            'https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3',
            true
        );
    }

    public function tearDown()
    {
        $this->url = null;
    }

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
        $url = $this->url_factory->createFromServer($server, true);
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

        $url = $this->url_factory->createFromServer($server);
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

        $this->url_factory->createFromServer($server);
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
        $url = $this->url_factory->createFromServer($server);
        $this->assertSame('https://127.0.0.1:23/toto?foo=bar', (string) $url);

        $server = array(
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );
        $url = $this->url_factory->createFromServer($server);

        $this->assertSame('https://127.0.0.1:23/', (string) $url);
    }

    public function testConstructor()
    {
        $expected = 'http://example.com:80/foo/bar?foo=bar#content';
        $this->assertSame($expected, (string) $this->url_factory->createFromString($expected));
        $this->assertSame('//example.com/', (string) $this->url_factory->createFromString('example.com'));
        $this->assertSame('//example.com/', (string) $this->url_factory->createFromString('//example.com'));
        $this->assertSame('/path/to/url.html', (string) $this->url_factory->createFromString('/path/to/url.html'));
        $this->assertSame(
            '//login@example.com/',
            (string) $this->url_factory->createFromString('login@example.com/')
        );
        $this->assertSame(
            '//login:pass@example.com/',
            (string) $this->url_factory->createFromString('login:pass@example.com/')
        );
        $this->assertSame(
            'http://login:pass@example.com/',
            (string) $this->url_factory->createFromString('http://login:pass@example.com/')
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromInvalidUrlKO()
    {
        $this->url_factory->createFromString("http://user@:80");
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateFromUrlKO()
    {
        $this->url_factory->createFromString(new StdClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodingType()
    {
        $this->assertSame(PHP_QUERY_RFC1738, $this->url_factory->getEncoding());
        $this->assertSame(PHP_QUERY_RFC3986, $this->url_factory->setEncoding(PHP_QUERY_RFC3986)->getEncoding());
        $this->url_factory->setEncoding('toto');
    }
}

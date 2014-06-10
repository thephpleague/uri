<?php

namespace League\Url\test;

use League\Url\Factory;
use PHPUnit_Framework_TestCase;
use StdClass;

class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = Factory::createFromString(
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

        $this->assertSame('https://example.com:23/', (string) Factory::createFromServer($server, true));

        $server = array(
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );

        $this->assertSame('https://127.0.0.1:23/', (string) Factory::createFromServer($server, true));
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

        Factory::createFromServer($server, true);
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

        $this->assertSame('https://127.0.0.1:23/toto?foo=bar', (string) Factory::createFromServer($server, true));

        $server = array(
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );

        $this->assertSame('https://127.0.0.1:23/', (string) Factory::createFromServer($server, true));
    }

    public function testSchemelessUrl()
    {
        $url = $this->url->setScheme(null);
        $this->assertNull($url->getScheme()->get());
    }

    public function testConstructor()
    {
        $expected = 'http://example.com:80/foo/bar?foo=bar#content';
        $this->assertSame($expected, (string) Factory::createFromString($expected, true));
        $this->assertSame('//example.com/', (string) Factory::createFromString('example.com', true));
        $this->assertSame('//example.com/', (string) Factory::createFromString('//example.com', true));
    }

    public function testSameValueAs()
    {
        $url1 = Factory::createFromString('example.com', true);
        $url2 = Factory::createFromString('//example.com', true);
        $url3 = Factory::createFromString('//example.com?foo=toto+le+heros', true, PHP_QUERY_RFC3986);
        $url4 = Factory::createFromString('//example.com?foo=toto+le+heros', true);
        $this->assertTrue($url1->sameValueAs($url2));
        $this->assertFalse($url3->sameValueAs($url2));
        $this->assertTrue($url3->sameValueAs($url4));
    }

    public function testConstructor3()
    {
        $this->assertSame('/path/to/url.html', (string) Factory::createFromString('/path/to/url.html', true));
    }

    public function testConstructor4()
    {
        $this->assertSame('//login@example.com/', (string) Factory::createFromString('login@example.com/', true));
        $this->assertSame('//login:pass@example.com/', (string) Factory::createFromString('login:pass@example.com/', true));
        $this->assertSame('http://login:pass@example.com/', (string) Factory::createFromString('http://login:pass@example.com/', true));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromInvalidUrlKO()
    {
        Factory::createFromString("http://user@:80", true);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateFromUrlKO()
    {
        Factory::createFromString(new StdClass, true);
    }
}

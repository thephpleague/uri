<?php

namespace League\Url\test;

use League\Url\Factory;
use League\Url\Components\Query;
use PHPUnit_Framework_TestCase;

class UrlImmutableTest extends PHPUnit_Framework_TestCase
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
        $this->url_factory = null;
    }

    public function testGetterAccess()
    {
        $this->assertInstanceof('League\Url\Components\Scheme', $this->url->getScheme());
        $this->assertInstanceof('League\Url\Components\User', $this->url->getUser());
        $this->assertInstanceof('League\Url\Components\Pass', $this->url->getPass());
        $this->assertInstanceof('League\Url\Components\Host', $this->url->getHost());
        $this->assertInstanceof('League\Url\Components\Port', $this->url->getPort());
        $this->assertInstanceof('League\Url\Components\Path', $this->url->getPath());
        $this->assertInstanceof('League\Url\Components\Query', $this->url->getQuery());
        $this->assertInstanceof('League\Url\Components\Fragment', $this->url->getFragment());
    }

    public function testSetterAccess()
    {
        $this->assertEquals($this->url, $this->url->setScheme('https'));
        $this->assertEquals($this->url, $this->url->setUser('login'));
        $this->assertEquals($this->url, $this->url->setPass('pass'));
        $this->assertEquals($this->url, $this->url->setHost('secure.example.com'));
        $this->assertEquals($this->url, $this->url->setPort(443));
        $this->assertEquals($this->url, $this->url->setPath('/test/query.php'));
        $this->assertEquals($this->url, $this->url->setQuery('?kingkong=toto'));
        $this->assertEquals($this->url, $this->url->setFragment('doc3'));
    }

    public function testSameValueAs()
    {
        $url1 = $this->url_factory->createFromString('example.com');
        $url2 = $this->url_factory->createFromString('//example.com', true);
        $this->url_factory->setEncoding(PHP_QUERY_RFC3986);
        $url3 = $this->url_factory->createFromString('//example.com?foo=toto+le+heros', true);
        $this->url_factory->setEncoding(PHP_QUERY_RFC1738);
        $url4 = $this->url_factory->createFromString('//example.com?foo=toto+le+heros');
        $this->assertTrue($url1->sameValueAs($url2));
        $this->assertFalse($url3->sameValueAs($url2));
        $this->assertTrue($url3->sameValueAs($url4));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodingType()
    {
        $this->assertSame(PHP_QUERY_RFC1738, $this->url->getEncoding());
        $this->assertSame(PHP_QUERY_RFC3986, $this->url->setEncoding(PHP_QUERY_RFC3986)->getEncoding());
        $this->url->setEncoding('toto');
    }
}

<?php

namespace League\Url\test;

use League\Url\UrlImmutable;
use League\Url\Url;
use League\Url\Components\Query;
use PHPUnit_Framework_TestCase;

/**
 * @group immutable
 */
class UrlImmutableTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = UrlImmutable::createFromUrl(
            'https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    public function tearDown()
    {
        $this->url = null;
    }

    public function testStringRepresentation()
    {
        $this->assertSame('https://login:pass@secure.example.com:443', $this->url->getBaseUrl());
        $this->assertSame('/test/query.php?kingkong=toto#doc3', $this->url->getRelativeUrl());
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testQuerySetter()
    {
        $this->assertSame(PHP_QUERY_RFC1738, $this->url->getQuery()->getEncoding());
        $url = $this->url->setQuery(array('foo' => 'hello world'), PHP_QUERY_RFC3986);
        $this->assertSame(PHP_QUERY_RFC3986, $url->getQuery()->getEncoding());
        $this->assertSame('foo=hello%20world', $url->getQuery()->get());
        $this->url->setQuery(array('foo' => 'hello world'), 'PHP_QUERY_RFC3986');
    }

    public function testSameValueAs()
    {
        $url1 = Url::createFromUrl('example.com');
        $url2 = UrlImmutable::createFromUrl('//example.com');
        $url3 = UrlImmutable::createFromUrl('//example.com?foo=toto+le+heros', PHP_QUERY_RFC3986);
        $url4 = Url::createFromUrl('//example.com?foo=toto+le+heros', PHP_QUERY_RFC1738);
        $this->assertTrue($url1->sameValueAs($url2));
        $this->assertFalse($url3->sameValueAs($url2));
        $this->assertTrue($url3->sameValueAs($url4));
    }
}

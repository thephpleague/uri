<?php

namespace League\Url\test;

use League\Url\Factory;
use League\Url\Components\Query;
use PHPUnit_Framework_TestCase;

class UrlImmutableTest extends PHPUnit_Framework_TestCase
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
        $url1 = Factory::createFromString('example.com');
        $url2 = Factory::createFromString('//example.com', true);
        $url3 = Factory::createFromString('//example.com?foo=toto+le+heros', true, PHP_QUERY_RFC3986);
        $url4 = Factory::createFromString('//example.com?foo=toto+le+heros');
        $this->assertTrue($url1->sameValueAs($url2));
        $this->assertFalse($url3->sameValueAs($url2));
        $this->assertTrue($url3->sameValueAs($url4));
    }
}

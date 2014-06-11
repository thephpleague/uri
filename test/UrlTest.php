<?php

namespace League\Url\test;

use League\Url\Factory;
use League\Url\Components\Query;
use PHPUnit_Framework_TestCase;

class UrlTest extends PHPUnit_Framework_TestCase
{
    private $url;

    private $url_factory;

    public function setUp()
    {
        $this->url_factory = new Factory;
        $this->url = $this->url_factory->createFromString(
            'https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
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
        $this->assertSame($this->url, $this->url->setScheme('https'));
        $this->assertSame($this->url, $this->url->setUser('login'));
        $this->assertSame($this->url, $this->url->setPass('pass'));
        $this->assertSame($this->url, $this->url->setHost('secure.example.com'));
        $this->assertSame($this->url, $this->url->setPort(443));
        $this->assertSame($this->url, $this->url->setPath('/test/query.php'));
        $this->assertSame($this->url, $this->url->setQuery('?kingkong=toto'));
        $this->assertSame($this->url, $this->url->setFragment('doc3'));
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

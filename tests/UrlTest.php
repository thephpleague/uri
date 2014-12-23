<?php

namespace League\Url\Test;

use League\Url\Url;
use PHPUnit_Framework_TestCase;

/**
 * @group url
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
        $this->assertSame($this->url, $this->url->setScheme('https'));
        $this->assertSame($this->url, $this->url->setUser('login'));
        $this->assertSame($this->url, $this->url->setPass('pass'));
        $this->assertSame($this->url, $this->url->setHost('secure.example.com'));
        $this->assertSame($this->url, $this->url->setPort(443));
        $this->assertSame($this->url, $this->url->setPath('/test/query.php'));
        $this->assertSame($this->url, $this->url->setQuery('?kingkong=toto'));
        $this->assertSame($this->url, $this->url->setFragment('doc3'));
    }
}

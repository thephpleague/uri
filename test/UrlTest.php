<?php

namespace League\Url\test;

use League\Url\Factory;
use League\Url\Components\Query;
use PHPUnit_Framework_TestCase;
use ArrayIterator;

class UrlTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = Factory::createFromString('https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3');
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

    public function testQuery()
    {
        $this->assertSame(
            'ali=baba',
            (string) $this->url->setQuery(array('ali' => 'baba'))->getQuery()
        );
        $this->assertSame(
            'ali=baba',
            (string) $this->url->setQuery(new ArrayIterator(array('ali' => 'baba')))->getQuery()
        );
    }

    public function testPath()
    {
        $this->url->setPath(array('shop', 'rev iew'));
        $this->assertSame('shop/rev%20iew', $this->url->getPath()->get());
        $this->assertNull($this->url->setPath(null)->getPath()->get());
        $this->url->setPath(new ArrayIterator(array('sullivent', 'wacowski')));
        $this->assertSame('sullivent/wacowski', $this->url->getPath()->get());
    }

    public function testHost()
    {
        $this->assertSame('shop.fremium.com', $this->url->setHost('.shop.fremium.com')->getHost()->get());
        $this->assertSame('shop.premium.org', $this->url->setHost(array('shop', 'premium', 'org'))->getHost()->get());
        $this->assertSame(
            'shop.premium.org',
            $this->url->setHost(new ArrayIterator(array('shop', 'premium', 'org')))->getHost()->get()
        );
        $this->assertNull($this->url->setHost(null)->getHost()->get());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodingType()
    {
        $this->assertSame(PHP_QUERY_RFC1738, $this->url->getEncodingType());
        $this->assertSame(PHP_QUERY_RFC3986, $this->url->setEncodingType(PHP_QUERY_RFC3986)->getEncodingType());
        $this->url->setEncodingType('toto');
    }

    public function testOtherComponents()
    {
        $this->assertSame(
            'https://sullivent:wacowski@secure.example.com:443/test/query.php?kingkong=toto#doc3',
            $this->url->setUser('sullivent')->setPass('wacowski')->__toString()
        );

        $this->assertSame(
            'http://sullivent:wacowski@secure.example.com/test/query.php?kingkong=toto#doc3',
            $this->url->setScheme('http')->setPort(null)->__toString()
        );

        $this->assertSame(
            'http://sullivent:wacowski@secure.example.com/test/query.php?kingkong=toto#payment',
            (string) $this->url->setFragment('payment')
        );
        $this->assertSame('sullivent', $this->url->getUser()->get());
        $this->assertSame('wacowski', $this->url->getPass()->get());
        $this->assertNull($this->url->getPort()->get());
        $this->assertSame('payment', $this->url->getFragment()->get());
    }
}

<?php

namespace League\Url\test;

use League\Url\Factory;
use League\Url\Components\Query;
use PHPUnit_Framework_TestCase;
use StdClass;
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

        $this->assertSame('https://example.com:23/', (string) Factory::createFromServer($server));

        $server = array(
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );

        $this->assertSame('https://127.0.0.1:23/', (string) Factory::createFromServer($server));
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

        Factory::createFromServer($server);
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

        $this->assertSame('https://127.0.0.1:23/toto?foo=bar', (string) Factory::createFromServer($server));

        $server = array(
            'SERVER_ADDR' => '127.0.0.1',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
        );

        $this->assertSame('https://127.0.0.1:23/', (string) Factory::createFromServer($server));
    }

    public function testSchemelessUrl()
    {
        $url = $this->url->setScheme(null);
        $this->assertNull($url->getScheme()->get());
    }

    public function testConstructor()
    {
        $expected = 'http://example.com:80/foo/bar?foo=bar#content';
        $this->assertSame($expected, (string) Factory::createFromString($expected));
        $this->assertSame('//example.com/', (string) Factory::createFromString('example.com'));
        $this->assertSame('//example.com/', (string) Factory::createFromString('//example.com'));
    }

    public function testSameValueAs()
    {
        $url1 = Factory::createFromString('example.com');
        $url2 = Factory::createFromString('//example.com');
        $url3 = Factory::createFromString('//example.com?foo=toto+le+heros', PHP_QUERY_RFC3986);
        $url4 = Factory::createFromString('//example.com?foo=toto+le+heros');
        $this->assertTrue($url1->sameValueAs($url2));
        $this->assertFalse($url3->sameValueAs($url2));
        $this->assertTrue($url3->sameValueAs($url4));
    }

    public function testConstructor3()
    {
        $this->assertSame('/path/to/url.html', (string) Factory::createFromString('/path/to/url.html'));
    }

    public function testConstructor4()
    {
        $this->assertSame('//login@example.com/', (string) Factory::createFromString('login@example.com/'));
        $this->assertSame('//login:pass@example.com/', (string) Factory::createFromString('login:pass@example.com/'));
        $this->assertSame('http://login:pass@example.com/', (string) Factory::createFromString('http://login:pass@example.com/'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromInvalidUrlKO()
    {
        Factory::createFromString("http://user@:80");
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateFromUrlKO()
    {
        Factory::createFromString(new StdClass);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBadScheme()
    {
        Factory::createFromString('ftp://example.com');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testQuery()
    {
        $this->assertSame('kingkong=toto&john=doe+the+john', (string) $this->url->modifyQuery(array('john' => 'doe the john'))->getQuery());
        $this->assertSame('kingkong=toto&john=doe+the+john', (string) $this->url->modifyQuery(new ArrayIterator(array('john' => 'doe the john')))->getQuery());
        $this->assertSame('kingkong=tata', (string) $this->url->modifyQuery('?kingkong=tata')->getQuery());
        $this->assertSame('kingkong=toto', (string) $this->url->modifyQuery('')->getQuery());
        $this->assertNull($this->url->setQuery(null)->getQuery()->get());
        $this->assertSame('ali=baba', (string) $this->url->setQuery(array('ali' => 'baba'))->getQuery());
        $this->assertSame('kingkong=toto', (string) $this->url->getQuery());
        $this->url->modifyQuery(new StdClass);
    }

    public function testPath()
    {
        $this->assertSame('master/test/query.php', (string) $this->url->prependPath('master')->getPath());
        $this->assertSame('query.php', (string) $this->url->removePath('test')->getPath());
        $this->assertSame('test/query.php', (string) $this->url->removePath('toto')->getPath());
        $this->assertSame('test/sullivent/query.php', (string) $this->url->appendPath('sullivent', 'test')->getPath());
        $this->assertSame('shop/checkout', (string) $this->url->setPath('/shop/checkout')->getPath());
        $this->assertSame('shop/rev%20iew', (string) $this->url->setPath(array('shop', 'rev iew'))->getPath());
        $this->assertNull($this->url->setPath(null)->getPath()->get());
        $this->assertSame('test/query.php', (string) $this->url->getPath());

        $this->assertSame('test/sullivent/wacowski/query.php', (string) $this->url->appendPath(new ArrayIterator(array('sullivent', 'wacowski')), 'test')->getPath());

        $url = $this->url
            ->prependPath('master')
            ->prependPath('master');

        $this->assertSame('master/slave/master/test/query.php', (string) $url->appendPath('slave', 'master', 0)->getPath());

        $url = $this->url
            ->appendPath('master', 'test')
            ->appendPath('master', 'test');

        $this->assertSame('test/slave/master/master/query.php', (string) $url->prependPath('slave', 'master', 0)->getPath());
    }

    public function testHost()
    {
        $this->assertSame('master.secure.example.com', $this->url->prependHost('master')->getHost()->get());
        $this->assertSame('example.com', $this->url->removeHost('secure')->getHost()->get());
        $this->assertSame('secure.example.com', $this->url->removeHost('toto')->getHost()->get());
        $this->assertSame('secure.shop.example.com', $this->url->appendHost('shop', 'secure')->getHost()->get());
        $this->assertSame('shop.fremium.com', $this->url->setHost('.shop.fremium.com')->getHost()->get());
        $this->assertSame('shop.premium.org', $this->url->setHost(array('shop', 'premium', 'org'))->getHost()->get());
        $this->assertSame('shop.premium.org', $this->url->setHost(new ArrayIterator(array('shop', 'premium', 'org')))->getHost()->get());
        $this->assertNull($this->url->setHost(null)->getHost()->get());
        $this->assertSame('secure.example.com', $this->url->getHost()->get());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEncodingType()
    {
        $this->assertSame(PHP_QUERY_RFC1738, $this->url->getEncodingType());
        $this->assertSame(PHP_QUERY_RFC3986, $this->url->setEncodingType(PHP_QUERY_RFC3986)->getEncodingType());
        $this->assertSame(PHP_QUERY_RFC1738, $this->url->setEncodingType('toto')->getEncodingType());
    }

    public function testOtherComponents()
    {
        $this->assertSame('https://sullivent:wacowski@secure.example.com:443/test/query.php?kingkong=toto#doc3', (string) $this->url->setUser('sullivent')->setPass('wacowski'));
        $this->assertSame('http://login:pass@secure.example.com/test/query.php?kingkong=toto#doc3', (string) $this->url->setScheme('http')->setPort(null));
        $this->assertSame('https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#payment', (string) $this->url->setFragment('payment'));
        $this->assertSame('login', $this->url->getUser()->get());
        $this->assertSame('pass', $this->url->getPass()->get());
        $this->assertSame(443, $this->url->getPort()->get());
        $this->assertSame('doc3', $this->url->getFragment()->get());
    }
}

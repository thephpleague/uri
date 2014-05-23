<?php

namespace League\Url\test;

use League\Url\Url;
use PHPUnit_Framework_TestCase;
use StdClass;
use ArrayIterator;

class UrlTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = new Url('https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3');
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

        $this->assertSame('https://example.com:23/', (string) Url::createFromServer($server));
    }

    public function testConstructor()
    {
        $expected = 'http://example.com:80/foo/bar?foo=bar#content';
        $this->assertSame($expected, (string) new Url($expected));
        $this->assertSame('//example.com/', (string) new Url('example.com'));
        $this->assertSame('//example.com/', (string) new Url('//example.com'));
        $this->assertSame('/path/to/url.html', (string) new Url('/path/to/url.html'));
        $this->assertSame('//login@example.com/', (string) new Url('login@example.com/'));
        $this->assertSame('//login:pass@example.com/', (string) new Url('login:pass@example.com/'));
        $this->assertSame('http://login:pass@example.com/', (string) new Url('http://login:pass@example.com/'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateFromInvalidUrlKO()
    {
        new Url("http://user@:80");
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateFromUrlKO()
    {
        new Url(new StdClass);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadHostCharacters()
    {
        $this->url->setHost('_bad.host.com');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBadHostLength()
    {
        $host = implode('', array_fill(0, 23, 'banana'));
        $this->url->appendHost($host, 'secure');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTooManyHostlabel()
    {
        $host = array_fill(0, 128, 'a');
        $this->url->setHost($host);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testHosttooLong()
    {
        $host = array_fill(0, 23, 'banana-slip');
        $this->url->setHost($host);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBadPath()
    {
        $this->url->setPath(new StdClass);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testBadScheme()
    {
        new Url('ftp://example.com');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testQuery()
    {
        $this->assertSame('kingkong=toto&john=doe+the+john', $this->url->modifyQuery(array('john' => 'doe the john'))->getQuery());
        $this->assertSame('kingkong=toto&john=doe+the+john', $this->url->modifyQuery(new ArrayIterator(array('john' => 'doe the john')))->getQuery());
        $this->assertSame('kingkong=tata', $this->url->modifyQuery('?kingkong=tata')->getQuery());
        $this->assertSame('kingkong=toto', $this->url->modifyQuery('')->getQuery());
        $this->assertNull($this->url->setQuery(null)->getQuery());
        $this->assertSame('ali=baba', $this->url->setQuery(array('ali' => 'baba'))->getQuery());
        $this->assertSame('kingkong=toto', $this->url->getQuery());
        $this->url->modifyQuery(new StdClass);
    }

    public function testPath()
    {
        $this->assertSame('master/test/query.php', $this->url->prependPath('master')->getPath());
        $this->assertSame('query.php', $this->url->removePath('test')->getPath());
        $this->assertSame('test/query.php', $this->url->removePath('toto')->getPath());
        $this->assertSame('test/sullivent/query.php', $this->url->appendPath('sullivent', 'test')->getPath());
        $this->assertSame('shop/checkout', $this->url->setPath('/shop/checkout')->getPath());
        $this->assertSame('shop/rev%20iew', $this->url->setPath(['shop', 'rev iew'])->getPath());
        $this->assertNull($this->url->setPath(null)->getPath());
        $this->assertSame('test/query.php', $this->url->getPath());

        $this->assertSame('test/sullivent/wacowski/query.php', $this->url->appendPath(new ArrayIterator(array('sullivent', 'wacowski')), 'test')->getPath());

        $url = $this->url
            ->prependPath('master')
            ->prependPath('master');

        $this->assertSame('master/slave/master/test/query.php', $url->appendPath('slave', 'master', 0)->getPath());

        $url = $this->url
            ->appendPath('master', 'test')
            ->appendPath('master', 'test');

        $this->assertSame('test/slave/master/master/query.php', $url->prependPath('slave', 'master', 0)->getPath());
    }

    public function testHost()
    {
        $this->assertSame('master.secure.example.com', $this->url->prependHost('master')->getHost());
        $this->assertSame('example.com', $this->url->removeHost('secure')->getHost());
        $this->assertSame('secure.example.com', $this->url->removeHost('toto')->getHost());
        $this->assertSame('secure.shop.example.com', $this->url->appendHost('shop', 'secure')->getHost());
        $this->assertSame('shop.fremium.com', $this->url->setHost('.shop.fremium.com')->getHost());
        $this->assertSame('shop.premium.org', $this->url->setHost(array('shop', 'premium', 'org'))->getHost());
        $this->assertSame('shop.premium.org', $this->url->setHost(new ArrayIterator(array('shop', 'premium', 'org')))->getHost());
        $this->assertNull($this->url->setHost(null)->getHost());
        $this->assertSame('secure.example.com', $this->url->getHost());
    }

    public function testParse()
    {
        $expected = array (
            'scheme' => 'https',
            'user' => 'login',
            'pass' => 'pass',
            'host' => 'secure.example.com',
            'port' => 443,
            'path' => 'test/query.php',
            'query' => 'kingkong=toto',
            'fragment' => 'doc3',
        );
        $this->assertSame($expected, $this->url->parse());
        $this->assertSame(Url::PHP_QUERY_RFC1738, $this->url->getEncodingType());
        $this->assertSame(Url::PHP_QUERY_RFC3986, $this->url->setEncodingType(Url::PHP_QUERY_RFC3986)->getEncodingType());
        $this->assertSame(Url::PHP_QUERY_RFC1738, $this->url->setEncodingType('toto')->getEncodingType());
    }

    public function testOtherComponents()
    {
        $this->assertSame('https://sullivent:wacowski@secure.example.com:443/test/query.php?kingkong=toto#doc3', (string) $this->url->setUser('sullivent')->setPass('wacowski'));
        $this->assertSame('http://login:pass@secure.example.com/test/query.php?kingkong=toto#doc3', (string) $this->url->setScheme('http')->setPort(null));
        $this->assertSame('https://login:pass@secure.example.com:443/test/query.php?kingkong=toto#payment', (string) $this->url->setFragment('payment'));
        $this->assertSame('login', $this->url->getUser());
        $this->assertSame('pass', $this->url->getPass());
        $this->assertSame(443, $this->url->getPort());
        $this->assertSame('doc3', $this->url->getFragment());
    }
}

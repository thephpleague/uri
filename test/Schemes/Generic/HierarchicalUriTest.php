<?php

namespace League\Uri\Test\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Components;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group uri
 */
class HierarchicalUriTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var HttpUri
     */
    private $uri;

    public function setUp()
    {
        $this->uri = HttpUri::createFromString(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    public function tearDown()
    {
        $this->uri = null;
    }

    public function testGetterAccess()
    {
        $this->assertSame($this->uri->getScheme(), $this->uri->scheme->__toString());
        $this->assertSame($this->uri->getUserInfo(), $this->uri->userInfo->__toString());
        $this->assertSame($this->uri->getHost(), $this->uri->host->__toString());
        $this->assertSame($this->uri->getPort(), $this->uri->port->toInt());
        $this->assertSame($this->uri->getPath(), $this->uri->path->__toString());
        $this->assertSame($this->uri->getQuery(), $this->uri->query->__toString());
        $this->assertSame($this->uri->getFragment(), $this->uri->fragment->__toString());
    }

    public function testKeepSameInstanceIfPropertyDoesNotChange()
    {
        $this->assertSame($this->uri, $this->uri->withScheme('http'));
        $this->assertSame($this->uri, $this->uri->withUserInfo('login', 'pass'));
        $this->assertSame($this->uri, $this->uri->withHost('secure.example.com'));
        $this->assertSame($this->uri, $this->uri->withPort(443));
        $this->assertSame($this->uri, $this->uri->withPath('/test/query.php'));
        $this->assertSame($this->uri, $this->uri->withQuery('kingkong=toto'));
        $this->assertSame($this->uri, $this->uri->withFragment('doc3'));
    }

    public function testCreateANewInstanceWhenPropertyChanges()
    {
        $this->assertNotEquals($this->uri, $this->uri->withScheme('https'));
        $this->assertNotEquals($this->uri, $this->uri->withUserInfo('login', null));
        $this->assertNotEquals($this->uri, $this->uri->withHost('shop.example.com'));
        $this->assertNotEquals($this->uri, $this->uri->withPort(81));
        $this->assertNotEquals($this->uri, $this->uri->withPath('/test/file.php'));
        $this->assertNotEquals($this->uri, $this->uri->withQuery('kingkong=tata'));
        $this->assertNotEquals($this->uri, $this->uri->withFragment('doc2'));
    }

    public function testGetAuthority()
    {
        $this->assertSame('login:pass@secure.example.com:443', $this->uri->getAuthority());
    }

    public function testGetUserInfo()
    {
        $this->assertSame('login:pass', $this->uri->getUserInfo());
    }

    public function testAutomaticUrlNormalization()
    {
        $raw = 'HtTpS://MaStEr.eXaMpLe.CoM:443/%7ejohndoe/%a1/in+dex.php?foo.bar=value#fragment';
        $normalized = 'https://master.example.com/~johndoe/%A1/in+dex.php?foo.bar=value#fragment';
        $this->assertSame($normalized, (string) HttpUri::createFromString($raw));
    }

    /**
     * @param $uri
     * @param $port
     * @dataProvider portProvider
     */
    public function testPort($uri, $port)
    {
        $this->assertSame($port, HttpUri::createFromString($uri)->getPort());
    }

    public function portProvider()
    {
        return [
            ['http://www.example.com:443/', 443],
            ['http://www.example.com:80/', null],
            ['http://www.example.com', null],
            ['//www.example.com:80/', 80],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithSchemeFailedWithUnsupportedScheme()
    {
        HttpUri::createFromString('http://example.com')->withScheme('telnet');
    }

    /**
     * @param $uri
     * @param $expected
     * @dataProvider pathFormattingProvider
     */
    public function testPathFormatting($uri, $expected)
    {
        $this->assertSame($expected, $uri->__toString());
    }

    public function pathFormattingProvider()
    {
        return [
            [new HttpUri(
                new Components\Scheme('http'),
                new Components\UserInfo(),
                new Components\Host('ExAmPLe.cOm'),
                new Components\Port(),
                new Components\HierarchicalPath('path/to/the/sky'),
                new Components\Query(),
                new Components\Fragment()
            ), 'http://example.com/path/to/the/sky'],
            [new HttpUri(
                new Components\Scheme(''),
                new Components\UserInfo(),
                new Components\Host(),
                new Components\Port(),
                new Components\HierarchicalPath('///path/to/the/sky'),
                new Components\Query(),
                new Components\Fragment()
            ), '/path/to/the/sky'],
        ];
    }

    /**
     * @dataProvider invalidURI
     * @expectedException \RuntimeException
     * @param $input
     */
    public function testCreateFromInvalidUrlKO($input)
    {
        HttpUri::createFromString($input);
    }

    public function invalidURI()
    {
        return [
            ['http://user@:80'],
            ['//user@:80'],
        ];
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testModificationFailed()
    {
        HttpUri::createFromString('http://example.com/path')
            ->withScheme('')
            ->withHost('')
            ->withPath('data:go');
    }
}

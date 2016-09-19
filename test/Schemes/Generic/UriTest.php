<?php

namespace League\Uri\Test\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Test\AbstractTestCase;

/**
 * @group uri
 */
class UriTest extends AbstractTestCase
{
    /**
     * @var HttpUri
     */
    private $uri;

    protected function setUp()
    {
        $this->uri = HttpUri::createFromString(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    protected function tearDown()
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowExceptionOnUnknowPropertySetting()
    {
        $this->uri->unknownProperty = true;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowExceptionOnUnknowPropertyUnsetting()
    {
        unset($this->uri->unknownProperty);
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
        $normalized = 'https://master.example.com/%7Ejohndoe/%A1/in+dex.php?foo.bar=value#fragment';
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
     * @dataProvider invalidURI
     * @expectedException InvalidArgumentException
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
     * @expectedException InvalidArgumentException
     */
    public function testModificationFailedWithUnsupportedType()
    {
        HttpUri::createFromString('http://example.com/path')->withQuery(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testModificationFailed()
    {
        HttpUri::createFromString('http://example.com/path')
            ->withScheme('')
            ->withHost('')
            ->withPath('data:go');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testModificationFailedWithEmptyAuthority()
    {
        HttpUri::createFromString('http://example.com/path')
            ->withScheme('')
            ->withHost('')
            ->withPath('//toto');
    }

    public function testEmptyValueDetection()
    {
        $expected = '//0:0@0/0?0#0';
        $this->assertSame($expected, HttpUri::createFromString($expected)->__toString());
    }

    /**
     * @supportsDebugInfo
     */
    public function testDebugInfo()
    {
        $this->assertInternalType('array', $this->uri->__debugInfo());
        ob_start();
        var_dump($this->uri);
        $res = ob_get_clean();
        $this->assertContains($this->uri->__toString(), $res);
    }
}

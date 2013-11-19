<?php

namespace Bakame\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateFromServer()
    {
        $server = [
            'PHP_SELF' => '',
            'REQUEST_URI' => '',
            'HTTPS' => 'on',
            'SERVER_PROTOCOL' => 'HTTP',
            'SERVER_PORT' => 23,
            'HTTP_HOST' => 'example.com',
        ];
        $url = Url::createFromServer($server);
        $this->assertSame('https://example.com:23', $url->__toString());
    }

    public function testCreateFromUrl()
    {
        $expected = '//example.com/foo/bar?foo=bar#content';
        $url = Url::createFromString($expected);
        $this->assertSame($expected, $url->__toString());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateFromUrlKO()
    {
        Url::createFromString(new \DateTime);
    }

    public function testGetter()
    {
        $expected = '//example.com/foo/bar?foo=bar#content';
        $url = Url::createFromString($expected);

        $this->assertSame(['foo' => 'bar'], $url->getQuery());
        $this->assertSame('bar', $url->getQuery('foo'));
        $this->assertNull($url->getQuery('barbaz'));
        $this->assertSame(['foo', 'bar'], $url->getPath());
        $this->assertSame('content', $url->getFragment());
        $this->assertSame(['example', 'com'], $url->getHost());
        $this->assertNull($url->getPort());
        $this->assertNull($url->getScheme());
        $this->assertSame(['user' => null, 'pass' => null], $url->getAuth());
        $this->assertNull($url->getAuth('user'));
        $this->assertNull($url->getAuth('foo'));
    }

    public function testSetter()
    {
        $expected = '//example.com/foo/bar?foo=bar#content';
        $url = Url::createFromString($expected);

        $url
            ->setQuery(['toto' => 'leheros'])
            ->setFragment('top')
            ->setPort('443')
            ->setPath('inscription', 'prepend')
            ->setPath('cool')
            ->setAuth(['user' => 'john', 'pass' => 'doe'])
            ->setHost('api', 'prepend')
            ->setHost('uk')
            ->setScheme('https');

        $this->assertSame(['toto' => 'leheros', 'foo' => 'bar'], $url->getQuery());
        $this->assertSame(['inscription', 'foo', 'bar', 'cool'], $url->getPath());
        $this->assertSame('top', $url->getFragment());
        $this->assertSame(['api', 'example', 'com', 'uk'], $url->getHost());
        $this->assertSame(443, $url->getPort());
        $this->assertSame('https', $url->getScheme());
        $this->assertSame(['user' => 'john', 'pass' => 'doe'], $url->getAuth());
        $this->assertSame('https://john:doe@api.example.com.uk:443/inscription/foo/bar/cool?toto=leheros&foo=bar#top', $url->__toString());
    }

    public function testRemoveInfo()
    {
        $expected = '//john:doe@example.com/foo/bar?foo=bar#content';
        $url = Url::createFromString($expected);

        $url
            ->unsetHost('com')
            ->setHost('be')
            ->unsetPath(['foo', 'bar'])
            ->setPath(['user', 'profile'])
            ->unsetQuery('foo')
            ->unsetAuth('pass')
            ->setAuth('user', 'jane')
            ->setQuery('action', 'hello')
            ->setScheme('https');

        $this->assertSame('https://jane@example.be/user/profile?action=hello#content', $url->__toString());
    }

    public function testClearQuery()
    {
        $expected = '//john:doe@example.com/foo/bar?foo=bar&toto=leheros&bar=baz#content';
        $url = Url::createFromString($expected);
        $url
            ->unsetHost()
            ->unsetPath()
            ->unsetQuery(['foo', 'toto'])
            ->unsetQuery()
            ->unsetAuth();

        $this->assertSame('//#content', $url->__toString());
    }
}

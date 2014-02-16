<?php

namespace Bakame\Url\Test;

use Bakame\Url\Factory;

class UrlTest extends \PHPUnit_Framework_TestCase
{

    public function testGetter()
    {
        $expected = '//example.com/foo/bar?foo=bar#content';
        $url = Factory::createUrlFromString($expected);

        $this->assertSame(array('foo' => 'bar'), $url->query()->all());
        $this->assertSame('bar', $url->query()->get('foo'));
        $this->assertNull($url->query()->get('barbaz'));
        $this->assertSame(array('foo', 'bar'), $url->path()->all());
        $this->assertSame('content', $url->getFragment());
        $this->assertSame(array('example', 'com'), $url->host()->all());
        $this->assertNull($url->getPort());
        $this->assertNull($url->getScheme());
        $this->assertNull($url->getUsername());
        $this->assertNull($url->getPassword());
    }

    public function testSetter()
    {
        $expected = '//example.com/foo/bar?foo=bar#content';
        $url = Factory::createUrlFromString($expected);

        $url->query()->set(array('toto' => 'leheros'));
        $url->path()
            ->set('inscription', 'prepend')
            ->set('cool');
        $url->host()
            ->set('api', 'prepend')
            ->set('uk');
        $url
            ->setFragment('top')
            ->setPort('443')
            ->setUsername('john')
            ->setPassword('doe')
            ->setScheme('https');

        $this->assertSame(array('foo' => 'bar', 'toto' => 'leheros'), $url->query()->all());
        $this->assertSame(array('inscription', 'foo', 'bar', 'cool'), $url->path()->all());
        $this->assertSame('top', $url->getFragment());
        $this->assertSame(array('api', 'example', 'com', 'uk'), $url->host()->all());
        $this->assertSame(443, $url->getPort());
        $this->assertSame('https', $url->getScheme());
        $this->assertSame('john', $url->getUsername());
        $this->assertSame('doe', $url->getPassword());
        $this->assertSame('https://john:doe@api.example.com.uk:443/inscription/foo/bar/cool?foo=bar&toto=leheros#top', $url->__toString());
    }

    public function testRemoveInfo()
    {
        $expected = '//john:doe@example.com/foo/bar?foo=bar#content';
        $url = Factory::createUrlFromString($expected);

        $url->host()
            ->remove('com')
            ->set('be');

        $url->path()
            ->remove(array('foo', 'bar'))
            ->set(array('user', 'profile'));

        $url->query()
            ->remove('foo')
            ->set('action', 'hello');

        $url
            ->setPassword(null)
            ->setFragment(null)
            ->setUsername('jane')
            ->setScheme('https');

        $this->assertSame('https://jane@example.be/user/profile?action=hello', $url->__toString());
    }

    public function testClear()
    {
        $expected = '//john:doe@example.com/foo/bar?foo=bar&toto=leheros&bar=baz#content';
        $url = Factory::createUrlFromString($expected);
        $url->host()->clear();
        $url->path()->clear();
        $url->query()->clear();
        $url
            ->setFragment()
            ->setUsername()
            ->setPassword()
            ->setPort();

        $this->assertSame('//', $url->__toString());
    }

    public function testCloning()
    {
        $expected = '//example.com/foo/bar?foo=bar#content';
        $url = Factory::createUrlFromString($expected);
        $clone = clone $url;
        $this->assertSame($expected, $clone->__toString());
        $clone->query()->set('toto', 'malabar');
        $this->assertCount(2, $clone->query());
        $this->assertCount(1, $url->query());
    }

    public function testScheme()
    {
        $url = Factory::createUrlFromString('svn://example.com/foo/bar?foo=bar#content');
        $this->assertNull($url->getScheme());
    }
}

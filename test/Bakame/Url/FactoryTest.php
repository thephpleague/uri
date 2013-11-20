<?php

namespace Bakame\Url;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

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
        $url = Factory::createUrlFromServer($server);
        $this->assertSame('https://example.com:23', $url->__toString());
    }

    public function testCreateFromString()
    {
        $expected = '//example.com/foo/bar?foo=bar#content';
        $url = Factory::createUrlFromString($expected);
        $this->assertSame($expected, $url->__toString());
    }

    public function testCreate()
    {
        $expected = 'https://john@example.com/path/to/you?foo=bar#top';
        $url = Factory::createUrl(
            'https',
            'john',
            null,
            'example.com',
            null,
            '/path/to/you',
            'foo=bar',
            'top'
        );
        $this->assertSame($expected, $url->__toString());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromInvalidUrlKO()
    {
        Factory::createUrlFromString("http://user@:80");
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreateFromUrlKO()
    {
        Factory::createUrlFromString(new \DateTime);
    }
}

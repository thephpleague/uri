<?php

namespace League\Uri\Test;

use League\Uri;
use League\Uri\Schemes\Http as HttpUri;
use PHPUnit_Framework_TestCase;

/**
 * @group functions
 */
class FunctionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider uriProvider
     */
    public function testStat($uri, $absolute_uri, $network_path, $absolute_path, $relative_path)
    {
        $stats = Uri\uri_get_meta_data($uri);
        $this->assertInternalType('array', $stats);
        $this->assertSame($absolute_uri, $stats['absolute_uri']);
        $this->assertSame($network_path, $stats['network_path']);
        $this->assertSame($absolute_path, $stats['absolute_path']);
        $this->assertSame($relative_path, $stats['relative_path']);
    }

    public function uriProvider()
    {
        return [
            'absolute uri' => [
                'uri' => HttpUri::createFromString('http://a/p?q#f'),
                'absolute_uri' => true,
                'network_path' => false,
                'absolute_path' => false,
                'relative_path' => false,
            ],
            'network relative uri' => [
                'uri' => HttpUri::createFromString('//a/p?q#f'),
                'absolute_uri' => false,
                'network_path' => true,
                'absolute_path' => false,
                'relative_path' => false,
            ],
            'path absolute uri' => [
                'uri' => HttpUri::createFromString('/p?q#f'),
                'absolute_uri' => false,
                'network_path' => false,
                'absolute_path' => true,
                'relative_path' => false,
            ],
            'path relative uri with non empty path' => [
                'uri' => HttpUri::createFromString('p?q#f'),
                'absolute_uri' => false,
                'network_path' => false,
                'absolute_path' => false,
                'relative_path' => true,
            ],
            'path relative uri with empty' => [
                'uri' => HttpUri::createFromString('?q#f'),
                'absolute_uri' => false,
                'network_path' => false,
                'absolute_path' => false,
                'relative_path' => true,
            ],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStatThrowsInvalidArgumentException()
    {
        Uri\uri_get_meta_data('http://example.com');
    }
}

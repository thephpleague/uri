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
    public function testStat($uri, $infos)
    {
        $this->assertSame($infos, Uri\uri_getinfo($uri));
    }

    public function uriProvider()
    {
        return [
            'absolute uri' => [
                'uri' => HttpUri::createFromString('http://a/p?q#f'),
                'infos' => [
                    'absolute_uri' => true,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => false,
                ],
            ],
            'network relative uri' => [
                'uri' => HttpUri::createFromString('//a/p?q#f'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => true,
                    'absolute_path' => false,
                    'relative_path' => false,
                ],
            ],
            'path absolute uri' => [
                'uri' => HttpUri::createFromString('/p?q#f'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => true,
                    'relative_path' => false,
                ],
            ],
            'path relative uri with non empty path' => [
                'uri' => HttpUri::createFromString('p?q#f'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                ],
            ],
            'path relative uri with empty' => [
                'uri' => HttpUri::createFromString('?q#f'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                ],
            ],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStatThrowsInvalidArgumentException()
    {
        Uri\uri_getinfo('http://example.com');
    }
}

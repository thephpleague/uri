<?php

namespace League\Uri\Test\Modifiers;

use League\Uri\Modifiers;
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
    public function testStat($uri, $base_uri, $infos)
    {
        $this->assertSame($infos, Modifiers\uri_reference($uri, $base_uri));
    }

    public function uriProvider()
    {
        return [
            'absolute uri' => [
                'uri' => HttpUri::createFromString('http://a/p?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => true,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => false,
                ],
            ],
            'network relative uri' => [
                'uri' => HttpUri::createFromString('//스타벅스코리아.com/p?q#f'),
                'base_uri' => HttpUri::createFromString('//xn--oy2b35ckwhba574atvuzkc.com/p?q#z'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => true,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => true,
                ],
            ],
            'path absolute uri' => [
                'uri' => HttpUri::createFromString('/p?q#f'),
                'base_uri' => HttpUri::createFromString('/p?a#f'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => true,
                    'relative_path' => false,
                    'same_document' => false,
                ],
            ],
            'path relative uri with non empty path' => [
                'uri' => HttpUri::createFromString('p?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                    'same_document' => false,
                ],
            ],
            'path relative uri with empty' => [
                'uri' => HttpUri::createFromString('?q#f'),
                'base_uri' => null,
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => false,
                    'absolute_path' => false,
                    'relative_path' => true,
                    'same_document' => false,
                ],
            ],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider failedUriProvider
     */
    public function testStatThrowsInvalidArgumentException($uri, $base_uri)
    {
        Modifiers\uri_reference($uri, $base_uri);
    }

    public function failedUriProvider()
    {
        return [
            'invalid uri' => [
                'uri' => HttpUri::createFromString('http://a/p?q#f'),
                'base_uri' => 'http://example.com',
            ],
            'invalid base uri' => [
                'uri' => 'http://example.com',
                'base_uri' => HttpUri::createFromString('//a/p?q#f'),
            ],
        ];
    }
}

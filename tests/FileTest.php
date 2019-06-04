<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Uri;

use League\Uri\Exceptions\SyntaxError;
use League\Uri\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @group file
 * @group uri
 * @coversDefaultClass League\Uri\Uri
 */
class FileTest extends TestCase
{
    /**
     * @covers ::formatHost
     */
    public function testDefaultConstructor(): void
    {
        self::assertSame('', (string) Uri::createFromString());
    }

    /**
     * @covers ::formatHost
     * @covers ::formatFilePath
     * @covers ::assertValidState
     * @covers ::isUriWithSchemeHostAndPathOnly
     *
     * @dataProvider validUrlProvider
     */
    public function testCreateFromString(string $uri, string $expected): void
    {
        self::assertSame($expected, (string) Uri::createFromString($uri));
    }

    public function validUrlProvider(): array
    {
        return [
            'relative path' => [
                '.././path/../is/./relative',
                '.././path/../is/./relative',
            ],
            'absolute path' => [
                '/path/is/absolute',
                '/path/is/absolute',
            ],
            'empty path' => [
                '',
                '',
            ],
            'with host' => [
                '//example.com/path',
                '//example.com/path',
            ],
            'with normalized host' => [
                '//ExAmpLe.CoM/path',
                '//example.com/path',
            ],
            'with empty host' => [
                '///path',
                '///path',
            ],
            'with scheme' => [
                'file://localhost/path',
                'file://localhost/path',
            ],
            'with normalized scheme' => [
                'FiLe://localhost/path',
                'file://localhost/path',
            ],
            'with empty host and scheme' => [
                'FiLe:///path',
                'file:///path',
            ],
            'with windows path' => [
                'file:///C|/demo',
                'file:///C:/demo',
            ],
        ];
    }

    /**
     * @dataProvider invalidUrlProvider
     * @covers ::assertValidState
     * @covers ::isUriWithSchemeHostAndPathOnly
     */
    public function testConstructorThrowsException(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString($uri);
    }

    public function invalidUrlProvider(): array
    {
        return [
            'no authority 1' => ['file:example.com'],
            'no authority 2' => ['file:/example.com'],
            'query string' => ['file://example.com?'],
            'fragment' => ['file://example.com#'],
            'user info' => ['file://user:pass@example.com'],
            'port' => ['file://example.com:42'],
        ];
    }
}

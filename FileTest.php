<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Uri;

use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(\League\Uri\Uri::class)]
#[Group('file')]
#[Group('uri')]
final class FileTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        self::assertSame('', (string) Uri::new());
    }

    #[DataProvider('validUrlProvider')]
    public function testCreateFromString(string $uri, string $expected): void
    {
        self::assertSame($expected, (string) Uri::new($uri));
    }

    public static function validUrlProvider(): array
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

    #[DataProvider('invalidUrlProvider')]
    public function testConstructorThrowsException(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::new($uri);
    }

    public static function invalidUrlProvider(): array
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

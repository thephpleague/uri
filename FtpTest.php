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

#[CoversClass(Uri::class)]
#[Group('ftp')]
#[Group('uri')]
final class FtpTest extends TestCase
{
    #[DataProvider('validUrlProvider')]
    public function testCreateFromString(string $uri, string $expected): void
    {
        self::assertSame($expected, (string) Uri::new($uri));
    }

    public static function validUrlProvider(): array
    {
        return [
            'with default port' => [
                'FtP://ExAmpLe.CoM:21/foo/bar',
                'ftp://example.com/foo/bar',
            ],
            'with user info' => [
                'ftp://login:pass@example.com/',
                'ftp://login:pass@example.com/',
            ],
            'with network path' => [
                '//ExAmpLe.CoM:80',
                '//example.com:80',
            ],
            'absolute path' => [
                '/path/to/my/file',
                '/path/to/my/file',
            ],
            'relative path' => [
                '.././path/../is/./relative',
                '.././path/../is/./relative',
            ],
            'empty string' => [
                '',
                '',
            ],
        ];
    }


    #[DataProvider('invalidUrlProvider')]
    public function testConstructorThrowInvalidArgumentException(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::new($uri);
    }

    public static function invalidUrlProvider(): array
    {
        return [
            //['http://example.com'],
            ['ftp:/example.com'],
            ['ftp:example.com'],
            ['ftp://example.com?query#fragment'],
        ];
    }

    public function testModificationFailedWithEmptyAuthority(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('ftp://example.com/path')
            ->withScheme(null)
            ->withHost(null)
            ->withPath('//toto');
    }

    #[DataProvider('portProvider')]
    public function testPort(string $uri, ?int $port): void
    {
        self::assertSame($port, Uri::new($uri)->getPort());
    }

    public static function portProvider(): array
    {
        return [
            ['ftp://www.example.com:443/', 443],
            ['ftp://www.example.com:21/', null],
            ['ftp://www.example.com', null],
            ['//www.example.com:21/', 21],
        ];
    }
}

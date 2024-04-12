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
#[Group('ws')]
#[Group('uri')]
class WsTest extends TestCase
{
    #[DataProvider('validUrlProvider')]
    public function testCreateFromString(string $input, string $expected): void
    {
        self::assertSame($expected, (string) Uri::new($input));
    }

    public static function validUrlProvider(): array
    {
        return [
            'with default port' => [
                'Ws://ExAmpLe.CoM:80/foo/bar?foo=bar',
                'ws://example.com/foo/bar?foo=bar',
            ],
            'with user info' => [
                'wss://login:pass@example.com/',
                'wss://login:pass@example.com/',
            ],
            'network path' => [
                '//ExAmpLe.CoM:21',
                '//example.com:21',
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
            ['wss:example.com'],
            ['wss:/example.com'],
            ['wss://example.com:80/foo/bar?foo=bar#content'],
        ];
    }

    public function testModificationFailedWithEmptyAuthority(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('wss://example.com/path')
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
            ['ws://www.example.com:443/', 443],
            ['ws://www.example.com:80/', null],
            ['ws://www.example.com', null],
            ['//www.example.com:80/', 80],
        ];
    }
}

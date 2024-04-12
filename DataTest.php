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
#[Group('data')]
#[Group('uri')]
final class DataTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        self::assertSame(
            'data:text/plain;charset=us-ascii,',
            Uri::new('data:')->toString()
        );
    }

    #[DataProvider('validUrlProvider')]
    public function testCreateFromString(string $uri, string $path): void
    {
        self::assertSame($path, Uri::new($uri)->getPath());
    }

    public static function validUrlProvider(): array
    {
        return [
            'simple string' => [
                'uri' => 'data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
                'path' => 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
            ],
            'string without mimetype' => [
                'uri' => 'data:,Bonjour%20le%20monde%21',
                'path' => 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
            ],
            'string without parameters' => [
                'uri' => 'data:text/plain,Bonjour%20le%20monde%21',
                'path' => 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21',
            ],
            'empty string' => [
                'uri' => 'data:,',
                'path' => 'text/plain;charset=us-ascii,',
            ],
            'binary data' => [
                'uri' => 'data:image/gif;charset=binary;base64,R0lGODlhIAAgAIABAP8AAP///yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAgACAAAAI5jI+py+0Po5y02ouzfqD7DwJUSHpjSZ4oqK7m5LJw/Ep0Hd1dG/OuvwKihCVianbbKJfMpvMJjWYKADs=',
                'path' => 'image/gif;charset=binary;base64,R0lGODlhIAAgAIABAP8AAP///yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAgACAAAAI5jI+py+0Po5y02ouzfqD7DwJUSHpjSZ4oqK7m5LJw/Ep0Hd1dG/OuvwKihCVianbbKJfMpvMJjWYKADs=',
            ],
        ];
    }

    #[DataProvider('invalidUrlProvider')]
    public function testCreateFromStringFailed(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::new($uri);
    }

    public static function invalidUrlProvider(): array
    {
        return [
            'invalid data' => ['data:image/png;base64,°28'],
        ];
    }


    #[DataProvider('invalidComponentProvider')]
    public function testCreateFromStringFailedWithWrongComponent(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::new($uri);
    }

    public static function invalidComponentProvider(): array
    {
        return [
            'invalid data' => ['data:image/png;base64,zzz28'],
            'invalid mime type' => ['data:image_png;base64,zzz'],
            'invalid parameter' => ['data:image/png;base64;base64,zzz'],
        ];
    }

    public function testCreateFromComponentsFailedWithInvalidArgumentException(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('data:image/png;base64,°28');
    }

    public function testCreateFromComponentsFailedInvalidMediatype(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('data:image/png;base64=toto;base64,dsqdfqfd');
    }

    public function testCreateFromComponentsFailedWithException(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21#fragment');
    }

    public function testWithPath(): void
    {
        $path = 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21';
        $uri = Uri::new('data:'.$path);
        self::assertSame($uri, $uri->withPath($path));
    }

    public function testSyntaxError(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http:text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
    }
}

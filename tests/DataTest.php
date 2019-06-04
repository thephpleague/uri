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
 * @group data
 * @group uri
 * @coversDefaultClass League\Uri\Uri
 */
class DataTest extends TestCase
{
    /**
     * @covers ::formatDataPath
     */
    public function testDefaultConstructor(): void
    {
        self::assertSame(
            'data:text/plain;charset=us-ascii,',
            (string) Uri::createFromString('data:')
        );
    }

    /**
     * @covers ::formatPath
     * @covers ::formatDataPath
     * @covers ::assertValidPath
     * @covers ::assertValidState
     *
     * @dataProvider validUrlProvider
     */
    public function testCreateFromString(string $uri, string $path): void
    {
        self::assertSame($path, Uri::createFromString($uri)->getPath());
    }

    public function validUrlProvider(): array
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

    /**
     * @covers ::formatDataPath
     * @covers ::assertValidPath
     * @covers ::assertValidState
     *
     * @dataProvider invalidUrlProvider
     */
    public function testCreateFromStringFailed(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString($uri);
    }

    public function invalidUrlProvider(): array
    {
        return [
            'invalid data' => ['data:image/png;base64,°28'],
        ];
    }


    /**
     * @covers ::formatDataPath
     * @covers ::assertValidPath
     * @covers ::assertValidState
     *
     * @dataProvider invalidComponentProvider
     */
    public function testCreateFromStringFailedWithWrongComponent(string $uri): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString($uri);
    }

    public function invalidComponentProvider(): array
    {
        return [
            'invalid data' => ['data:image/png;base64,zzz28'],
            'invalid mime type' => ['data:image_png;base64,zzz'],
            'invalid parameter' => ['data:image/png;base64;base64,zzz'],
        ];
    }

    /**
     * @covers ::assertValidPath
     * @covers ::formatDataPath
     * @covers ::assertValidState
     */
    public function testCreateFromComponentsFailedWithInvalidArgumentException(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('data:image/png;base64,°28');
    }

    /**
     * @covers ::assertValidPath
     * @covers ::validateParameter
     * @covers ::formatDataPath
     * @covers ::assertValidState
     */
    public function testCreateFromComponentsFailedInvalidMediatype(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('data:image/png;base64=toto;base64,dsqdfqfd');
    }

    public function testCreateFromComponentsFailedWithException(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('data:text/plain;charset=us-ascii,Bonjour%20le%20monde%21#fragment');
    }

    /**
     * @covers ::assertValidPath
     * @covers ::formatDataPath
     * @covers ::assertValidState
     */
    public function testWithPath(): void
    {
        $path = 'text/plain;charset=us-ascii,Bonjour%20le%20monde%21';
        $uri = Uri::createFromString('data:'.$path);
        self::assertSame($uri, $uri->withPath($path));
    }

    /**
     * @covers ::assertValidState
     */
    public function testSyntaxError(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http:text/plain;charset=us-ascii,Bonjour%20le%20monde%21');
    }
}

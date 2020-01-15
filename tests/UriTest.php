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
use TypeError;

/**
 * @group uri
 * @coversDefaultClass League\Uri\Uri
 */
class UriTest extends TestCase
{
    /**
     * @var Uri
     */
    private $uri;

    protected function setUp(): void
    {
        $this->uri = Uri::createFromString(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    protected function tearDown(): void
    {
        unset($this->uri);
    }

    /**
     * @covers ::__toString
     * @covers ::formatHost
     * @covers ::formatRegisteredName
     * @covers ::formatQueryAndFragment
     * @covers ::formatPort
     * @covers ::formatUserInfo
     * @covers ::formatScheme
     */
    public function testAutomaticUrlNormalization(): void
    {
        $raw = 'HtTpS://MaStEr.B%c3%A9b%c3%a9.eXaMpLe.CoM:/%7ejohndoe/%a1/in+dex.php?fào.%bar=v%61lue#fragment';
        $normalized = 'https://master.xn--bb-bjab.example.com/%7ejohndoe/%a1/in+dex.php?f%C3%A0o.%bar=v%61lue#fragment';
        self::assertSame($normalized, (string) Uri::createFromString($raw));
    }

    /**
     * @covers ::__toString
     * @covers ::formatHost
     */
    public function testAutomaticUrlNormalizationBis(): void
    {
        self::assertSame(
            'http://xn--bb-bjab.be./path',
            (string) Uri::createFromString('http://Bébé.BE./path')
        );
    }

    /**
     * @covers ::__toString
     * @covers ::formatScheme
     */
    public function testConstructingUrisWithSchemesWithNonLeadingDigits(): void
    {
        $uri = 's3://somebucket/somefile.txt';
        self::assertSame($uri, (string) Uri::createFromString($uri));
    }

    /**
     * @covers ::__toString
     * @covers ::formatScheme
     * @covers ::withScheme
     */
    public function testSettingSchemesWithNonLeadingDigits(): void
    {
        $uri = 'http://somebucket/somefile.txt';
        $expected_uri = 's3://somebucket/somefile.txt';
        self::assertSame($expected_uri, (string) Uri::createFromString($uri)->withScheme('s3'));
    }

    /**
     * @covers ::getUriString
     * @covers ::__toString
     * @covers ::formatUserInfo
     * @covers ::formatQueryAndFragment
     */
    public function testPreserveComponentsOnInstantiation(): void
    {
        $uri = 'http://:@example.com?#';
        self::assertSame($uri, (string) Uri::createFromString($uri));
    }

    /**
     * @covers ::getScheme
     * @covers ::withScheme
     */
    public function testScheme(): void
    {
        self::assertSame('http', $this->uri->getScheme());
        self::assertSame($this->uri, $this->uri->withScheme('http'));
        self::assertNotEquals($this->uri, $this->uri->withScheme('https'));
        self::assertSame(
            '//login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3',
            (string) $this->uri->withScheme(null)
        );
    }

    /**
     * @covers ::getUserInfo
     * @covers ::withUserInfo
     * @covers ::formatUserInfo
     */
    public function testUserInfo(): void
    {
        self::assertSame('login:pass', $this->uri->getUserInfo());
        self::assertSame($this->uri, $this->uri->withUserInfo('login', 'pass'));

        $newUri = $this->uri->withUserInfo('login', null);
        self::assertNotEquals($this->uri, $newUri);

        $altUri = $this->uri->withUserInfo(null);
        self::assertNotEquals($this->uri, $altUri);

        self::assertSame('http://secure.example.com:443/test/query.php?kingkong=toto#doc3', (string) $altUri);
    }

    /**
     * @covers ::getHost
     * @covers ::withHost
     * @covers ::formatHost
     * @covers ::formatIp
     * @covers ::formatRegisteredName
     */
    public function testHost(): void
    {
        self::assertSame('secure.example.com', $this->uri->getHost());
        self::assertSame($this->uri, $this->uri->withHost('secure.example.com'));
        self::assertNotEquals($this->uri, $this->uri->withHost('[::1]'));
    }

    /**
     * @covers ::getAuthority
     */
    public function testGetAuthority(): void
    {
        self::assertSame('login:pass@secure.example.com:443', $this->uri->getAuthority());
    }

    /**
     * @covers ::withUserInfo
     * @covers ::withPort
     * @covers ::withScheme
     * @covers ::withHost
     */
    public function testRemoveAuthority(): void
    {
        $uri_with_host = (string) $this->uri
            ->withUserInfo(null)
            ->withPort(null)
            ->withScheme(null)
            ->withHost(null);
        self::assertSame('/test/query.php?kingkong=toto#doc3', $uri_with_host);
    }

    /**
     * @covers ::getPort
     * @covers ::withPort
     * @covers ::formatPort
     */
    public function testPort(): void
    {
        self::assertSame(443, $this->uri->getPort());
        self::assertSame($this->uri, $this->uri->withPort(443));
        self::assertNotEquals($this->uri, $this->uri->withPort(81));
        self::assertSame(
            'http://login:pass@secure.example.com/test/query.php?kingkong=toto#doc3',
            (string) $this->uri->withPort(null)
        );
    }

    /**
     * @covers ::getPath
     * @covers ::withPath
     */
    public function testPath(): void
    {
        self::assertSame('/test/query.php', $this->uri->getPath());
        self::assertSame($this->uri, $this->uri->withPath('/test/query.php'));
        self::assertNotEquals($this->uri, $this->uri->withPath('/test/file.php'));
        self::assertSame(
            'http://login:pass@secure.example.com:443?kingkong=toto#doc3',
            (string) $this->uri->withPath('')
        );
    }

    /**
     * @covers ::getQuery
     * @covers ::withQuery
     */
    public function testQuery(): void
    {
        self::assertSame('kingkong=toto', $this->uri->getQuery());
        self::assertSame($this->uri, $this->uri->withQuery('kingkong=toto'));
        self::assertNotEquals($this->uri, $this->uri->withQuery('kingkong=tata'));
        self::assertSame(
            'http://login:pass@secure.example.com:443/test/query.php#doc3',
            (string) $this->uri->withQuery(null)
        );
    }

    /**
     * @covers ::getFragment
     * @covers ::withFragment
     */
    public function testFragment(): void
    {
        self::assertSame('doc3', $this->uri->getFragment());
        self::assertSame($this->uri, $this->uri->withFragment('doc3'));
        self::assertNotEquals($this->uri, $this->uri->withFragment('doc2'));
        self::assertSame(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto',
            (string) $this->uri->withFragment(null)
        );
    }

    /**
     * @covers ::getIDNAErrors
     * @covers ::formatHost
     */
    public function testCannotConvertInvalidHost(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://_b%C3%A9bé.be-/foo/bar');
    }

    public function testWithSchemeFailedWithInvalidSchemeValue(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://example.com')->withScheme('tété');
    }

    /**
     * @covers ::filterString
     */
    public function testWithInvalidCharacters(): void
    {
        self::expectException(TypeError::class);
        Uri::createFromString('')->withPath(date_create());
    }

    /**
     * @covers ::assertValidState
     */
    public function testWithPathFailedWithInvalidChars(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://example.com')->withPath('#24');
    }

    /**
     * @covers ::assertValidState
     */
    public function testWithPathFailedWithInvalidPathRelativeToTheAuthority(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://example.com')->withPath('foo/bar');
    }

    /**
     * @covers ::formatRegisteredName
     * @covers ::withHost
     */
    public function testModificationFailedWithInvalidHost(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://example.com/path')->withHost('%23');
    }

    /**
     * @covers ::assertValidState
     * @dataProvider missingAuthorityProvider
     */
    public function testModificationFailedWithMissingAuthority(string $path): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://example.com/path')
            ->withScheme(null)
            ->withHost(null)
            ->withPath($path);
    }

    /**
     * @covers ::assertValidState
     */
    public function missingAuthorityProvider(): array
    {
        return [
            ['data:go'],
            ['//data'],
        ];
    }

    /**
     * @covers ::__toString
     * @covers ::formatHost
     * @covers ::formatRegisteredName
     * @covers ::formatQueryAndFragment
     * @covers ::formatPort
     * @covers ::formatUserInfo
     */
    public function testEmptyValueDetection(): void
    {
        $expected = '//0:0@0/0?0#0';
        self::assertSame($expected, Uri::createFromString($expected)->__toString());
    }

    public function testPathDetection(): void
    {
        $expected = 'foo/bar:';
        self::assertSame($expected, Uri::createFromString($expected)->getPath());
    }

    /**
     * @covers ::filterString
     * @covers ::withPath
     */
    public function testWithPathThrowTypeErrorOnWrongType(): void
    {
        self::expectException(TypeError::class);
        Uri::createFromString('https://example.com')->withPath(null);
    }

    /**
     * @dataProvider setStateDataProvider
     *
     * @covers ::__set_state
     */
    public function testSetState(Uri $uri): void
    {
        self::assertEquals($uri, eval('return '.var_export($uri, true).';'));
    }

    public function setStateDataProvider(): array
    {
        return [
            'all components' => [Uri::createFromString('https://a:b@c:442/d?q=r#f')],
            'without scheme' => [Uri::createFromString('//a:b@c:442/d?q=r#f')],
            'without userinfo' => [Uri::createFromString('https://c:442/d?q=r#f')],
            'without port' => [Uri::createFromString('https://a:b@c/d?q=r#f')],
            'without path' => [Uri::createFromString('https://a:b@c:442?q=r#f')],
            'without query' => [Uri::createFromString('https://a:b@c:442/d#f')],
            'without fragment' => [Uri::createFromString('https://a:b@c:442/d?q=r')],
            'without pass' => [Uri::createFromString('https://a@c:442/d?q=r#f')],
            'without authority' => [Uri::createFromString('/d?q=r#f')],
       ];
    }

    /**
     * @covers ::__debugInfo
     */
    public function testDebugInfo(): void
    {
        $uri = Uri::createFromString('https://a:b@c:442/d?q=r#f');
        $debugInfo = $uri->__debugInfo();
        self::assertSame('a:***', $debugInfo['user_info']);
        self::assertCount(7, $debugInfo);
    }

    public function testJsonSerialize(): void
    {
        $uri = Uri::createFromString('https://a:b@c:442/d?q=r#f');
        self::assertJsonStringEqualsJsonString(json_encode($uri->__toString()), json_encode($uri));
    }

    /**
     * @covers ::createFromComponents
     * @covers ::formatRegisteredName
     */
    public function testCreateFromComponents(): void
    {
        $uri = '//0:0@0/0?0#0';
        self::assertEquals(
            Uri::createFromComponents(parse_url($uri)),
            Uri::createFromString($uri)
        );
    }

    /**
     * @covers ::formatPort
     * @covers ::withPort
     */
    public function testModificationFailedWithInvalidPort(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://example.com/path')->withPort(-1);
    }

    /**
     * @covers ::formatPort
     * @covers ::withPort
     */
    public function testModificationFailedWithInvalidPort2(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromString('http://example.com/path')->withPort('-1');
    }

    /**
     * @covers ::formatIp
     */
    public function testCreateFromComponentsHandlesScopedIpv6(): void
    {
        $expected = '[fe80:1234::%251]';
        self::assertSame(
            $expected,
            Uri::createFromComponents(['host' => $expected])->getHost()
        );
    }

    /**
     * @covers ::formatIp
     */
    public function testCreateFromComponentsHandlesIpvFuture(): void
    {
        $expected = '[v1.ZZ.ZZ]';
        self::assertSame(
            $expected,
            Uri::createFromComponents(['host' => $expected])->getHost()
        );
    }


    /**
     * @covers ::formatIp
     */
    public function testCreateFromComponentsThrowsOnInvalidIpvFuture(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => '[v4.1.2.3]']);
    }

    /**
     * @covers ::filterString
     */
    public function testCreateFromComponentsThrowsExceptionWithInvalidChars(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents()->withFragment("\n\rtoto");
    }

    /**
     * @covers ::formatIp
     */
    public function testCreateFromComponentsThrowsException(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => '[127.0.0.1]']);
    }

    /**
     * @covers ::formatIp
     */
    public function testCreateFromComponentsThrowsException2(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => '[127.0.0.1%251]']);
    }

    /**
     * @covers ::formatIp
     */
    public function testCreateFromComponentsThrowsException3(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => '[fe80:1234::%25 1]']);
    }

    /**
     * @covers ::formatIp
     */
    public function testCreateFromComponentsThrowsException4(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => '[::1%251]']);
    }

    /**
     * @covers ::formatRegisteredName
     * @covers ::getIDNAErrors
     */
    public function testCreateFromComponentsThrowsException5(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => 'a⒈com']);
    }

    /**
     * @covers ::formatRegisteredName
     * @covers ::getIDNAErrors
     */
    public function testCreateFromComponentsThrowsException6(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => 'xn--3']);
    }

    /**
     * @covers ::formatRegisteredName
     */
    public function testCreateFromComponentsThrowsException7(): void
    {
        self::expectException(SyntaxError::class);
        Uri::createFromComponents(['host' => str_repeat('A', 255)]);
    }

    /**
     * @covers ::formatRegisteredName
     */
    public function testCreateFromComponentsWorksWithPunycode(): void
    {
        $uri = Uri::createFromComponents(['host' => 'xn--mgbh0fb.xn--kgbechtv']);
        self::assertSame('xn--mgbh0fb.xn--kgbechtv', $uri->getHost());
    }

    /**
     * @covers ::formatPath
     */
    public function testReservedCharsInPathUnencoded(): void
    {
        $uri = Uri::createFromString()
            ->withHost('api.linkedin.com')
            ->withScheme('https')
            ->withPath('/v1/people/~:(first-name,last-name,email-address,picture-url)');

        self::assertStringContainsString(
            '/v1/people/~:(first-name,last-name,email-address,picture-url)',
            (string) $uri
        );
    }

    /**
     * @dataProvider userInfoProvider
     * @param ?string $credential
     */
    public function testWithUserInfoEncodesUsernameAndPassword(string $user, ?string $credential, string $expected): void
    {
        $uri = Uri::createFromString('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo($user, $credential);
        self::assertSame($expected, $new->getUserInfo());
    }

    public function userInfoProvider(): array
    {
        return [
            'no password' => ['login:', null, 'login%3A'],
            'password with delimiter' => ['login', 'password@', 'login:password%40'],
            'valid-chars' => ['foo', 'bar', 'foo:bar'],
            'colon'       => ['foo:bar', 'baz:bat', 'foo%3Abar:baz:bat'],
            'at'          => ['user@example.com', 'cred@foo', 'user%40example.com:cred%40foo'],
            'percent'     => ['%25', '%25', '%25:%25'],
            'invalid-enc' => ['%ZZ', '%GG', '%25ZZ:%25GG'],
        ];
    }
}

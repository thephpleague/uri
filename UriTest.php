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

use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Port;
use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use TypeError;

#[CoversClass(Uri::class)]
#[Group('uri')]
class UriTest extends TestCase
{
    private Uri $uri;

    protected function setUp(): void
    {
        $this->uri = Uri::new(
            'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3'
        );
    }

    protected function tearDown(): void
    {
        unset($this->uri);
    }

    public function testAutomaticUrlNormalization(): void
    {
        $raw = 'HtTpS://MaStEr.B%c3%A9b%c3%a9.eXaMpLe.CoM:/%7ejohndoe/%a1/in+dex.php?fào.%bar=v%61lue#fragment';
        $normalized = 'https://master.xn--bb-bjab.example.com/%7ejohndoe/%a1/in+dex.php?f%C3%A0o.%bar=v%61lue#fragment';
        $components = [
            'scheme' => 'https',
            'user' => null,
            'pass' => null,
            'host' => 'master.xn--bb-bjab.example.com',
            'port' => null,
            'path' => '/%7ejohndoe/%a1/in+dex.php',
            'query' => 'f%C3%A0o.%bar=v%61lue',
            'fragment' => 'fragment',
        ];
        $uri = Uri::new($raw);

        self::assertSame($normalized, $uri->toString());
        self::assertSame($components, $uri->toComponents());
    }

    public function testAutomaticUrlNormalizationBis(): void
    {
        self::assertSame(
            'http://xn--bb-bjab.be./path',
            (string) Uri::new('http://Bébé.BE./path')
        );
    }

    public function testConstructingUrisWithSchemesWithNonLeadingDigits(): void
    {
        $uri = 's3://somebucket/somefile.txt';
        self::assertSame($uri, (string) Uri::new($uri));
    }

    public function testSettingSchemesWithNonLeadingDigits(): void
    {
        $uri = 'http://somebucket/somefile.txt';
        $expected_uri = 's3://somebucket/somefile.txt';
        self::assertSame($expected_uri, (string) Uri::new($uri)->withScheme('s3'));
    }

    public function testPreserveComponentsOnInstantiation(): void
    {
        $uri = 'http://:@example.com?#';
        self::assertSame($uri, (string) Uri::new($uri));
    }

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

    public function testUserInfo(): void
    {
        self::assertSame('login:pass', $this->uri->getUserInfo());
        self::assertSame($this->uri, $this->uri->withUserInfo('login', 'pass'));

        $newUri = $this->uri->withUserInfo('login');
        self::assertNotEquals($this->uri, $newUri);

        $altUri = $this->uri->withUserInfo(null);
        self::assertNotEquals($this->uri, $altUri);

        self::assertSame('http://secure.example.com:443/test/query.php?kingkong=toto#doc3', (string) $altUri);
    }

    public function testHost(): void
    {
        self::assertSame('secure.example.com', $this->uri->getHost());
        self::assertSame($this->uri, $this->uri->withHost('secure.example.com'));
        self::assertNotEquals($this->uri, $this->uri->withHost('[::1]'));
    }

    public function testGetAuthority(): void
    {
        self::assertSame('login:pass@secure.example.com:443', $this->uri->getAuthority());
    }

    public function testRemoveAuthority(): void
    {
        $uri_with_host = (string) $this->uri
            ->withUserInfo(null)
            ->withPort(null)
            ->withScheme(null)
            ->withHost(null);
        self::assertSame('/test/query.php?kingkong=toto#doc3', $uri_with_host);
    }

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

    public function testCannotConvertInvalidHost(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://_b%C3%A9bé.be-/foo/bar');
    }

    public function testWithSchemeFailedWithInvalidSchemeValue(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com')->withScheme('tété');
    }

    public function testWithPathFailedWithInvalidChars(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com')->withPath('#24');
    }

    public function testWithPathFailedWithInvalidPathRelativeToTheAuthority(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com')->withPath('foo/bar');
    }

    public function testModificationFailedWithInvalidHost(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com/path')->withHost('%23');
    }

    #[DataProvider('missingAuthorityProvider')]
    public function testModificationFailedWithMissingAuthority(string $path): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com/path')
            ->withScheme(null)
            ->withHost(null)
            ->withPath($path);
    }

    public static function missingAuthorityProvider(): array
    {
        return [
            ['data:go'],
            ['//data'],
        ];
    }

    public function testEmptyValueDetection(): void
    {
        $expected = '//0:0@0/0?0#0';
        self::assertSame($expected, Uri::new($expected)->toString());
    }

    public function testPathDetection(): void
    {
        $expected = 'foo/bar:';
        self::assertSame($expected, Uri::new($expected)->getPath());
    }

    public function testWithPathThrowTypeErrorOnWrongType(): void
    {
        self::expectException(TypeError::class);

        Uri::new('https://example.com')->withPath(null); /* @phpstan-ignore-line */
    }

    public function testJsonSerialize(): void
    {
        $uri = Uri::new('https://a:b@c:442/d?q=r#f');

        /** @var string $uriString */
        $uriString = json_encode((string) $uri);
        /** @var string $uriJsonString */
        $uriJsonString = json_encode($uri);

        self::assertJsonStringEqualsJsonString($uriString, $uriJsonString);
    }

    public function testCreateFromComponents(): void
    {
        $uri = '//0:0@0/0?0#0';
        self::assertEquals(
            Uri::fromComponents(parse_url($uri)),
            Uri::new($uri)
        );
    }

    public function testModificationFailedWithInvalidPort(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com/path')->withPort(-1);
    }

    public function testModificationFailedWithInvalidPort2(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com/path')->withPort('-1'); /* @phpstan-ignore-line */
    }

    public function testCreateFromComponentsHandlesScopedIpv6(): void
    {
        $expected = '[fe80:1234::%251]';
        self::assertSame(
            $expected,
            Uri::fromComponents(['host' => $expected])->getHost()
        );
    }

    public function testCreateFromComponentsHandlesIpvFuture(): void
    {
        $expected = '[v1.ZZ.ZZ]';
        self::assertSame(
            $expected,
            Uri::fromComponents(['host' => $expected])->getHost()
        );
    }

    public function testCreateFromComponentsThrowsOnInvalidIpvFuture(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents(['host' => '[v4.1.2.3]']);
    }

    public function testCreateFromComponentsThrowsExceptionWithInvalidChars(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents()->withFragment("\n\rtoto");
    }

    public function testCreateFromComponentsThrowsException(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents(['host' => '[127.0.0.1]']);
    }

    public function testCreateFromComponentsThrowsException2(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents(['host' => '[127.0.0.1%251]']);
    }

    public function testCreateFromComponentsThrowsException3(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents(['host' => '[fe80:1234::%25 1]']);
    }

    public function testCreateFromComponentsThrowsException4(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents(['host' => '[::1%251]']);
    }

    public function testCreateFromComponentsThrowsException5(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents(['host' => 'a⒈com']);
    }

    public function testCreateFromComponentsShouldNotThrowWithAsciiToUnicodeConversion(): void
    {
        self::assertSame(
            'xn--3',
            Uri::fromComponents(['host' => 'Xn--3'])->getHost()
        );
    }

    public function testCreateFromComponentsThrowsException7(): void
    {
        self::expectException(SyntaxError::class);
        Uri::fromComponents(['host' => str_repeat('A', 255)]);
    }

    public function testCreateFromComponentsWorksWithPunycode(): void
    {
        $uri = Uri::fromComponents(['host' => 'xn--mgbh0fb.xn--kgbechtv']);
        self::assertSame('xn--mgbh0fb.xn--kgbechtv', $uri->getHost());
    }

    public function testReservedCharsInPathUnencoded(): void
    {
        $uri = Uri::new()
            ->withHost('api.linkedin.com')
            ->withScheme('https')
            ->withPath('/v1/people/~:(first-name,last-name,email-address,picture-url)');

        self::assertStringContainsString(
            '/v1/people/~:(first-name,last-name,email-address,picture-url)',
            (string) $uri
        );
    }

    public function testUnreservedCharsInPathUnencoded(): void
    {
        $uri = Uri::new('http://www.example.com/')
            ->withPath('/h"ell\'o/./wor ld<i>/%25abc%xyz');

        self::assertSame(
            "/h%22ell'o/./wor%20ld%3Ci%3E/%25abc%25xyz",
            $uri->getPath()
        );
    }

    #[DataProvider('userInfoProvider')]
    public function testWithUserInfoEncodesUsernameAndPassword(string $user, ?string $credential, string $expected): void
    {
        $uri = Uri::new('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo($user, $credential);
        self::assertSame($expected, $new->getUserInfo());
    }

    public static function userInfoProvider(): array
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

    public function testIssue167ExceptionReasonMisleadingMessage(): void
    {
        self::expectException(SyntaxError::class);
        self::expectExceptionMessage('The uri `file://example.org:80/home/jsmith/foo.txt` is invalid for the `file` scheme.');

        Uri::new('file://example.org:80/home/jsmith/foo.txt');
    }

    public function testIssue171TheEmptySchemeShouldThrow(): void
    {
        self::expectException(SyntaxError::class);
        self::expectExceptionMessage('The scheme `` is invalid.');

        Uri::new('domain.com')->withScheme('');
    }

    public function testItStripMultipleLeadingSlashOnGetPath(): void
    {
        $uri = Uri::new('https://example.com///miscillaneous.tld');

        self::assertSame('https://example.com///miscillaneous.tld', (string) $uri);
        self::assertSame('/miscillaneous.tld', $uri->getPath());

        $modifiedUri = $uri->withPath('///foobar');

        self::assertSame('https://example.com///foobar', (string) $modifiedUri);
        self::assertSame('/foobar', $modifiedUri->getPath());
        self::assertSame('//example.com///foobar', (string) $modifiedUri->withScheme(null));

        $this->expectException(SyntaxError::class);
        $modifiedUri->withScheme(null)->withHost(null);
    }

    public function testItPreservesMultipleLeadingSlashesOnMutation(): void
    {
        $uri = Uri::new('https://www.example.com///google.com');
        self::assertSame('https://www.example.com///google.com', (string) $uri);
        self::assertSame('/google.com', $uri->getPath());

        $modifiedUri =  $uri->withPath('/google.com');
        self::assertSame('https://www.example.com/google.com', (string) $modifiedUri);
        self::assertSame('/google.com', $modifiedUri->getPath());

        $modifiedUri2 =  $uri->withPath('///google.com');
        self::assertSame('https://www.example.com///google.com', (string) $modifiedUri2);
        self::assertSame('/google.com', $modifiedUri2->getPath());
    }

    public function testItCanBeUpdatedWithAnUriComponent(): void
    {
        $uri = Uri::new('https://www.example.com/')
            ->withPath(HierarchicalPath::fromAbsolute('do', 'you', 'love', 'brahms'));

        self::assertSame('https://www.example.com/do/you/love/brahms', $uri->toString());
    }

    public function testItThrowsWhenTheUriComponentValueIsNull(): void
    {
        $this->expectException(SyntaxError::class);

        Uri::new('https://www.example.com/')->withPath(Port::new());
    }
}

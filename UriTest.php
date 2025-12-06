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

use GuzzleHttp\Psr7\Utils;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Port;
use League\Uri\Exceptions\SyntaxError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface as Psr7UriInterface;
use TypeError;

use function base64_encode;
use function dirname;
use function file_get_contents;
use function serialize;
use function str_repeat;
use function stream_context_create;
use function unlink;
use function unserialize;

#[CoversClass(HostRecord::class)]
#[CoversClass(UriString::class)]
#[CoversClass(Uri::class)]
#[Group('uri')]
class UriTest extends TestCase
{
    private const BASE_URI = 'http://a/b/c/d;p?q';

    private Uri $uri;

    private string $rootPath;

    public function setUp(): void
    {
        $this->rootPath = dirname(__DIR__).'/test_files';
        $this->uri = Uri::new('http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3');
    }

    protected function tearDown(): void
    {
        unset($this->uri);
    }

    public function testAutomaticUrlNormalization(): void
    {
        $raw = 'HtTpS://MaStEr.B%c3%A9b%c3%a9.eXaMpLe.CoM:/%7ejohndoe/%a1/in+dex.php?fào.%bar=v%61lue#fragment';
        $normalized = 'https://master.b%C3%A9b%C3%A9.example.com/%7ejohndoe/%a1/in+dex.php?f%C3%A0o.%bar=v%61lue#fragment';
        $components = [
            'scheme' => 'https',
            'user' => null,
            'pass' => null,
            'host' => 'master.b%C3%A9b%C3%A9.example.com',
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
        self::assertSame(
            'http://example.com/%2324',
            Uri::new('http://example.com')->withPath('#24')->toString()
        );
    }

    public function testWithPathFailedWithInvalidPathRelativeToTheAuthority(): void
    {
        $uri = Uri::new('http://example.com')->withPath('foo/bar');
        self::assertSame('http://example.com/foo/bar', $uri->toString());
    }

    public function testModificationFailedWithInvalidHost(): void
    {
        self::expectException(SyntaxError::class);
        Uri::new('http://example.com/path')->withHost(':');
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

        /** @var non-empty-string $uriString */
        $uriString = json_encode((string) $uri);
        /** @var non-empty-string $uriJsonString */
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
        $host = str_repeat('A', 255);

        self::assertSame(strtolower($host), Uri::fromComponents(['host' => $host])->getHost());
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
        self::assertSame('///miscillaneous.tld', $uri->getPath());

        $modifiedUri = $uri->withPath('///foobar');

        self::assertSame('https://example.com///foobar', (string) $modifiedUri);
        self::assertSame('///foobar', $modifiedUri->getPath());
        self::assertSame('//example.com///foobar', (string) $modifiedUri->withScheme(null));
        self::assertSame(
            '/.///foobar',
            $modifiedUri
                ->withScheme(null)
                ->withHost(null)
                ->toString()
        );
    }

    public function testItPreservesMultipleLeadingSlashesOnMutation(): void
    {
        $uri = Uri::new('https://www.example.com///google.com');
        self::assertSame('https://www.example.com///google.com', (string) $uri);
        self::assertSame('///google.com', $uri->getPath());

        $modifiedUri =  $uri->withPath('/google.com');
        self::assertSame('https://www.example.com/google.com', (string) $modifiedUri);
        self::assertSame('/google.com', $modifiedUri->getPath());

        $modifiedUri2 =  $uri->withPath('///google.com');
        self::assertSame('https://www.example.com///google.com', (string) $modifiedUri2);
        self::assertSame('///google.com', $modifiedUri2->getPath());
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

    #[DataProvider('resolveProvider')]
    public function testCreateResolve(string $baseUri, string $uri, string $expected): void
    {
        self::assertSame($expected, Uri::new($baseUri)->resolve($uri)->toString());
    }

    public static function resolveProvider(): array
    {
        return [
            'base uri'                => [self::BASE_URI, '',              self::BASE_URI],
            'scheme'                  => [self::BASE_URI, 'http://d/e/f',  'http://d/e/f'],
            'path 1'                  => [self::BASE_URI, 'g',             'http://a/b/c/g'],
            'path 2'                  => [self::BASE_URI, './g',           'http://a/b/c/g'],
            'path 3'                  => [self::BASE_URI, 'g/',            'http://a/b/c/g/'],
            'path 4'                  => [self::BASE_URI, '/g',            'http://a/g'],
            'authority'               => [self::BASE_URI, '//g',           'http://g'],
            'query'                   => [self::BASE_URI, '?y',            'http://a/b/c/d;p?y'],
            'path + query'            => [self::BASE_URI, 'g?y',           'http://a/b/c/g?y'],
            'fragment'                => [self::BASE_URI, '#s',            'http://a/b/c/d;p?q#s'],
            'path + fragment'         => [self::BASE_URI, 'g#s',           'http://a/b/c/g#s'],
            'path + query + fragment' => [self::BASE_URI, 'g?y#s',         'http://a/b/c/g?y#s'],
            'single dot 1'            => [self::BASE_URI, '.',             'http://a/b/c/'],
            'single dot 2'            => [self::BASE_URI, './',            'http://a/b/c/'],
            'single dot 3'            => [self::BASE_URI, './g/.',         'http://a/b/c/g/'],
            'single dot 4'            => [self::BASE_URI, 'g/./h',         'http://a/b/c/g/h'],
            'double dot 1'            => [self::BASE_URI, '..',            'http://a/b/'],
            'double dot 2'            => [self::BASE_URI, '../',           'http://a/b/'],
            'double dot 3'            => [self::BASE_URI, '../g',          'http://a/b/g'],
            'double dot 4'            => [self::BASE_URI, '../..',         'http://a/'],
            'double dot 5'            => [self::BASE_URI, '../../',        'http://a/'],
            'double dot 6'            => [self::BASE_URI, '../../g',       'http://a/g'],
            'double dot 7'            => [self::BASE_URI, '../../../g',    'http://a/g'],
            'double dot 8'            => [self::BASE_URI, '../../../../g', 'http://a/g'],
            'double dot 9'            => [self::BASE_URI, 'g/../h' ,       'http://a/b/c/h'],
            'mulitple slashes'        => [self::BASE_URI, 'foo////g',      'http://a/b/c/foo////g'],
            'complex path 1'          => [self::BASE_URI, ';x',            'http://a/b/c/;x'],
            'complex path 2'          => [self::BASE_URI, 'g;x',           'http://a/b/c/g;x'],
            'complex path 3'          => [self::BASE_URI, 'g;x?y#s',       'http://a/b/c/g;x?y#s'],
            'complex path 4'          => [self::BASE_URI, 'g;x=1/./y',     'http://a/b/c/g;x=1/y'],
            'complex path 5'          => [self::BASE_URI, 'g;x=1/../y',    'http://a/b/c/y'],
            'dot segments presence 1' => [self::BASE_URI, '/./g',          'http://a/g'],
            'dot segments presence 2' => [self::BASE_URI, '/../g',         'http://a/g'],
            'dot segments presence 3' => [self::BASE_URI, 'g.',            'http://a/b/c/g.'],
            'dot segments presence 4' => [self::BASE_URI, '.g',            'http://a/b/c/.g'],
            'dot segments presence 5' => [self::BASE_URI, 'g..',           'http://a/b/c/g..'],
            'dot segments presence 6' => [self::BASE_URI, '..g',           'http://a/b/c/..g'],
            'origin uri without path' => ['http://h:b@a', 'b/../y',        'http://h:b@a/y'],
            'not same origin'         => [self::BASE_URI, 'ftp://a/b/c/d', 'ftp://a/b/c/d'],
        ];
    }

    public function testRelativizeIsNotMade(): void
    {
        $uri = '//path#fragment';

        self::assertEquals($uri, Uri::new('https://example.com/path')->relativize($uri)->toString());
    }

    #[DataProvider('relativizeProvider')]
    public function testRelativize(string $uri, string $resolved, string $expected): void
    {
        self::assertSame(
            $expected,
            Uri::new(Http::new($uri))->relativize($resolved)->toString()
        );
    }

    public static function relativizeProvider(): array
    {
        return [
            'different scheme'        => [self::BASE_URI,       'https://a/b/c/d;p?q',   'https://a/b/c/d;p?q'],
            'different authority'     => [self::BASE_URI,       'https://g/b/c/d;p?q',   'https://g/b/c/d;p?q'],
            'empty uri'               => [self::BASE_URI,       '',                      ''],
            'same uri'                => [self::BASE_URI,       self::BASE_URI,          ''],
            'same path'               => [self::BASE_URI,       'http://a/b/c/d;p',      'd;p'],
            'parent path 1'           => [self::BASE_URI,       'http://a/b/c/',         './'],
            'parent path 2'           => [self::BASE_URI,       'http://a/b/',           '../'],
            'parent path 3'           => [self::BASE_URI,       'http://a/',             '../../'],
            'parent path 4'           => [self::BASE_URI,       'http://a',              '../../'],
            'sibling path 1'          => [self::BASE_URI,       'http://a/b/c/g',        'g'],
            'sibling path 2'          => [self::BASE_URI,       'http://a/b/c/g/h',      'g/h'],
            'sibling path 3'          => [self::BASE_URI,       'http://a/b/g',          '../g'],
            'sibling path 4'          => [self::BASE_URI,       'http://a/g',            '../../g'],
            'query'                   => [self::BASE_URI,       'http://a/b/c/d;p?y',    '?y'],
            'fragment'                => [self::BASE_URI,       'http://a/b/c/d;p?q#s',  '#s'],
            'path + query'            => [self::BASE_URI,       'http://a/b/c/g?y',      'g?y'],
            'path + fragment'         => [self::BASE_URI,       'http://a/b/c/g#s',      'g#s'],
            'path + query + fragment' => [self::BASE_URI,       'http://a/b/c/g?y#s',    'g?y#s'],
            'empty segments'          => [self::BASE_URI,       'http://a/b/c/foo////g', 'foo////g'],
            'empty segments 1'        => [self::BASE_URI,       'http://a/b////c/foo/g', '..////c/foo/g'],
            'relative single dot 1'   => [self::BASE_URI,       '.',                     '.'],
            'relative single dot 2'   => [self::BASE_URI,       './',                    './'],
            'relative double dot 1'   => [self::BASE_URI,       '..',                    '..'],
            'relative double dot 2'   => [self::BASE_URI,       '../',                   '../'],
            'path with colon 1'       => ['http://a/',          'http://a/d:p',          './d:p'],
            'path with colon 2'       => [self::BASE_URI,       'http://a/b/c/g/d:p',    'g/d:p'],
            'scheme + auth 1'         => ['http://a',           'http://a?q#s',          '?q#s'],
            'scheme + auth 2'         => ['http://a/',          'http://a?q#s',          '/?q#s'],
            '2 relative paths 1'      => ['a/b',                '../..',                 '../..'],
            '2 relative paths 2'      => ['a/b',                './.',                   './.'],
            '2 relative paths 3'      => ['a/b',                '../c',                  '../c'],
            '2 relative paths 4'      => ['a/b',                'c/..',                  'c/..'],
            '2 relative paths 5'      => ['a/b',                'c/.',                   'c/.'],
            'baseUri with query'      => ['/a/b/?q',            '/a/b/#h',               './#h'],
            'targetUri with fragment' => ['/',                  '/#h',                   '#h'],
            'same document'           => ['/',                  '/',                     ''],
            'same URI normalized'     => ['http://a',           'http://a/',             ''],
        ];
    }

    /**
     * @param array<bool> $infos
     */
    #[DataProvider('uriProvider')]
    public function testInfo(
        Psr7UriInterface|Uri $uri,
        Psr7UriInterface|Uri|null $base_uri,
        array $infos
    ): void {
        if (null !== $base_uri) {
            self::assertSame($infos['same_document'], Uri::new($base_uri)->isSameDocument($uri));
        }
        self::assertSame($infos['relative_path'], Uri::new($uri)->isRelativePath());
        self::assertSame($infos['absolute_path'], Uri::new($uri)->isAbsolutePath());
        self::assertSame($infos['absolute_uri'], Uri::new($uri)->isAbsolute());
        self::assertSame($infos['network_path'], Uri::new($uri)->isNetworkPath());
    }

    public static function uriProvider(): array
    {
        return [
            'absolute uri' => [
                'uri' => Http::new('http://a/p?q#f'),
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
                'uri' => Http::new('//스타벅스코리아.com/p?q#f'),
                'base_uri' => Http::new('//xn--oy2b35ckwhba574atvuzkc.com/p?q#z'),
                'infos' => [
                    'absolute_uri' => false,
                    'network_path' => true,
                    'absolute_path' => false,
                    'relative_path' => false,
                    'same_document' => true,
                ],
            ],
            'path relative uri with non empty path' => [
                'uri' => Http::new('p?q#f'),
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
                'uri' => Http::new('?q#f'),
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

    public function testIsFunctionsThrowsTypeError(): void
    {
        self::assertTrue(Uri::new('http://example.com')->isAbsolute());
        self::assertFalse(Uri::new('http://example.com')->isNetworkPath());
        self::assertTrue(Uri::new('/example.com')->isAbsolutePath());
        self::assertTrue(Uri::new('example.com#foobar')->isRelativePath());
    }

    #[DataProvider('sameValueAsProvider')]
    public function testSameValueAs(Psr7UriInterface|Uri $uri1, Psr7UriInterface|Uri $uri2, bool $expected): void
    {
        self::assertSame($expected, Uri::new($uri2)->isSameDocument($uri1));
    }

    public static function sameValueAsProvider(): array
    {
        return [
            '2 disctincts URIs' => [
                Http::new('http://example.com'),
                Uri::new('ftp://example.com'),
                false,
            ],
            '2 identical URIs' => [
                Http::new('http://example.com'),
                Http::new('http://example.com'),
                true,
            ],
            '2 identical URIs after removing dot segment' => [
                Http::new('http://example.org/~foo/'),
                Http::new('http://example.ORG/bar/./../~foo/'),
                true,
            ],
            '2 distincts relative URIs' => [
                Http::new('~foo/'),
                Http::new('../~foo/'),
                false,
            ],
            '2 identical relative URIs' => [
                Http::new('../%7efoo/'),
                Http::new('../~foo/'),
                true,
            ],
            '2 identical URIs after normalization (1)' => [
                Http::new('HtTp://مثال.إختبار:80/%7efoo/%7efoo/'),
                Http::new('http://xn--mgbh0fb.xn--kgbechtv/%7Efoo/~foo/'),
                true,
            ],
            '2 identical URIs after normalization (2)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com'),
                true,
            ],
            '2 identical URIs after normalization (3)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com:/'),
                true,
            ],
            '2 identical URIs after normalization (4)' => [
                Http::new('http://www.example.com'),
                Http::new('http://www.example.com:80'),
                true,
            ],
        ];
    }

    #[DataProvider('getOriginProvider')]
    public function testGetOrigin(Psr7UriInterface|Uri|string $uri, ?string $expectedOrigin): void
    {
        self::assertSame($expectedOrigin, Uri::new($uri)->getOrigin());
    }

    public static function getOriginProvider(): array
    {
        return [
            'http uri' => [
                'uri' => Uri::new('https://example.com/path?query#fragment'),
                'expectedOrigin' => 'https://example.com',
            ],
            'http uri with non standard port' => [
                'uri' => Uri::new('https://example.com:81/path?query#fragment'),
                'expectedOrigin' => 'https://example.com:81',
            ],
            'relative uri' => [
                'uri' => Uri::new('//example.com:81/path?query#fragment'),
                'expectedOrigin' => null,
            ],
            'absolute uri with user info' => [
                'uri' => Uri::new('https://user:pass@example.com:81/path?query#fragment'),
                'expectedOrigin' => 'https://example.com:81',
            ],
            'opaque URI' => [
                'uri' => Uri::new('mailto:info@thephpleague.com'),
                'expectedOrigin' => null,
            ],
            'file URI' => [
                'uri' => Uri::new('file:///usr/bin/test'),
                'expectedOrigin' => null,
            ],
            'blob' => [
                'uri' => Uri::new('blob:https://mozilla.org:443/1f6a188e-c21b-11f0-8de9-0242ac120002'),
                'expectedOrigin' => 'https://mozilla.org',
            ],
            'compressed ipv6' => [
                'uri' => 'https://[1050:0000:0000:0000:0005:0000:300c:326b]:443/',
                'expectedOrigin' => 'https://[1050::5:0:300c:326b]',
            ],
            'normalized ipv4 non decimal notation' => [
                'uri' => 'https://0xc0a821/foo/bar/',
                'expectedOrigin' => 'https://0.192.168.33',
            ],
            'unknown scheme for cross origin' => [
                'uri' => 'blob:null/1f6a188e-c21b-11f0-8de9-0242ac120002',
                'expectedOrigin' => null,
            ],
        ];
    }

    #[DataProvider('getCrossOriginExamples')]
    public function testIsCrossOrigin(string $original, string $modified, bool $expected): void
    {
        self::assertSame($expected, !Uri::new($original)->isSameOrigin($modified));
    }

    /**
     * @return array<string, array{0:string, 1:string, 2:bool}>
     */
    public static function getCrossOriginExamples(): array
    {
        return [
            'different path' => ['http://example.com/123', 'http://example.com/', false],
            'same port with default value (1)' => ['https://example.com/123', 'https://example.com:443/', false],
            'same port with default value (2)' => ['ws://example.com:80/123', 'ws://example.com/', false],
            'same explicit port' => ['wss://example.com:443/123', 'wss://example.com:443/', false],
            'same origin with i18n host' => ['https://xn--bb-bjab.be./path', 'https://Bébé.BE./path', false],
            'same origin using a blob' => ['blob:https://mozilla.org:443/1f6a188e-c21b-11f0-8de9-0242ac120002', 'https://mozilla.org/123', false],
            'different scheme' => ['https://example.com/123', 'ftp://example.com/', true],
            'different host' => ['ftp://example.com/123', 'ftp://www.example.com/123', true],
            'different port implicit' => ['https://example.com/123', 'https://example.com:81/', true],
            'different port explicit' => ['https://example.com:80/123', 'https://example.com:81/', true],
            'same scheme different port' => ['https://example.com:443/123', 'https://example.com:444/', true],
            'comparing two opaque URI' => ['ldap://ldap.example.net', 'ldap://ldap.example.net', true],
            'comparing a URI with an origin and one with an opaque origin' => ['https://example.com:443/123', 'ldap://ldap.example.net', true],
            'cross origin using a blob' => ['blob:http://mozilla.org:443/1f6a188e-c21b-11f0-8de9-0242ac120002', 'https://mozilla.org/123', true],
        ];
    }

    #[DataProvider('idnUriProvider')]
    public function testItReturnsTheCorrectUriString(string $expected, string $input): void
    {
        self::assertSame($expected, Uri::new($input)->toDisplayString());
    }

    public static function idnUriProvider(): iterable
    {
        yield 'basic uri stays the same' => [
            'expected' => 'http://example.com/foo/bar',
            'input' => 'http://example.com/foo/bar',
        ];

        yield 'idn host are changed' => [
            'expected' => 'http://bébé.be',
            'input' => 'http://xn--bb-bjab.be',
        ];

        yield 'idn host are the same' => [
            'expected' => 'http://bébé.be',
            'input' => 'http://bébé.be',
        ];

        yield 'the rest of the URI is not affected and uses RFC3986 rules' => [
            'expected' => 'http://bébé.be?q=toto le héros',
            'input' => 'http://bébé.be:80?q=toto%20le%20h%C3%A9ros',
        ];
    }

    #[DataProvider('unixpathProvider')]
    public function testReturnsUnixPath(?string $expected, string $input): void
    {
        self::assertSame($expected, Uri::new($input)->toUnixPath());
        self::assertSame($expected, Uri::new(Utils::uriFor($input))->toUnixPath());
    }

    public static function unixpathProvider(): array
    {
        return [
            'relative path' => [
                'expected' => 'path',
                'input' => 'path',
            ],
            'absolute path' => [
                'expected' => '/path',
                'input' => 'file:///path',
            ],
            'path with empty char' => [
                'expected' => '/path empty/bar',
                'input' => 'file:///path%20empty/bar',
            ],
            'relative path with dot segments' => [
                'expected' => 'path/./relative',
                'input' => 'path/./relative',
            ],
            'absolute path with dot segments' => [
                'expected' => '/path/./../relative',
                'input' => 'file:///path/./../relative',
            ],
            'unsupported scheme' => [
                'expected' => null,
                'input' => 'http://example.com/foo/bar',
            ],
        ];
    }

    #[DataProvider('windowLocalPathProvider')]
    public function testReturnsWindowsPath(?string $expected, string $input): void
    {
        self::assertSame($expected, Uri::new($input)->toWindowsPath());
    }

    public static function windowLocalPathProvider(): array
    {
        return [
            'relative path' => [
                'expected' => 'path',
                'input' => 'path',
            ],
            'relative path with dot segments' => [
                'expected' => 'path\.\relative',
                'input' => 'path/./relative',
            ],
            'absolute path' => [
                'expected' => 'c:\windows\My Documents 100%20\foo.txt',
                'input' => 'file:///c:/windows/My%20Documents%20100%2520/foo.txt',
            ],
            'windows relative path' => [
                'expected' => 'c:My Documents 100%20\foo.txt',
                'input' => 'file:///c:My%20Documents%20100%2520/foo.txt',
            ],
            'absolute path with `|`' => [
                'expected' => 'c:\windows\My Documents 100%20\foo.txt',
                'input' => 'file:///c:/windows/My%20Documents%20100%2520/foo.txt',
            ],
            'windows relative path with `|`' => [
                'expected' => 'c:My Documents 100%20\foo.txt',
                'input' => 'file:///c:My%20Documents%20100%2520/foo.txt',
            ],
            'absolute path with dot segments' => [
                'expected' => '\path\.\..\relative',
                'input' => '/path/./../relative',
            ],
            'absolute UNC path' => [
                'expected' => '\\\\server\share\My Documents 100%20\foo.txt',
                'input' => 'file://server/share/My%20Documents%20100%2520/foo.txt',
            ],
            'unsupported scheme' => [
                'expected' => null,
                'input' => 'http://example.com/foo/bar',
            ],
        ];
    }

    #[DataProvider('rfc8089UriProvider')]
    public function testReturnsRFC8089UriString(?string $expected, string $input): void
    {
        self::assertSame($expected, Uri::new($input)->toRfc8089());
    }

    public static function rfc8089UriProvider(): iterable
    {
        return [
            'localhost' => [
                'expected' => 'file:/etc/fstab',
                'input' => 'file://localhost/etc/fstab',
            ],
            'empty authority' => [
                'expected' => 'file:/etc/fstab',
                'input' => 'file:///etc/fstab',
            ],
            'file with authority' => [
                'expected' => 'file://yesman/etc/fstab',
                'input' => 'file://yesman/etc/fstab',
            ],
            'invalid scheme' => [
                'expected' => null,
                'input' => 'foobar://yesman/etc/fstab',
            ],
        ];
    }

    #[Test]
    #[DataProvider('providesUriToDisplay')]
    public function it_will_generate_the_display_uri_string(string $input, string $output): void
    {
        self::assertSame($output, Uri::new($input)->toDisplayString());
    }

    public static function providesUriToDisplay(): iterable
    {
        yield 'empty string' => [
            'input' => '',
            'output' => '',
        ];

        yield 'host IPv6' => [
            'input' => 'https://[fe80:0000:0000:0000:0000:0000:0000:000a%25en1]/foo/bar',
            'output' => 'https://[fe80:0000:0000:0000:0000:0000:0000:000a%en1]/foo/bar',
        ];

        yield 'IPv6 gets expanded if needed' => [
            'input' => 'http://bébé.be?q=toto%20le%20h%C3%A9ros',
            'output' => 'http://bébé.be?q=toto le héros',
        ];

        yield 'complex URI' => [
            'input' => 'https://xn--google.com/secret/../search?q=%F0%9F%8D%94',
            'output' => 'https://䕮䕵䕶䕱.com/secret/../search?q=🍔',
        ];

        yield 'basic uri stays the same' => [
            'input' => 'http://example.com/foo/bar',
            'output' => 'http://example.com/foo/bar',
        ];

        yield 'idn host are changed' => [
            'input' => 'http://xn--bb-bjab.be',
            'output' => 'http://bébé.be',
        ];

        yield 'idn host are the same' => [
            'input' => 'http://bébé.be',
            'output' => 'http://bébé.be',
        ];
    }

    #[Test]
    public function it_can_update_the_user_component(): void
    {
        self::assertSame('user', Uri::new('example://host/path?query')->withUsername('user')->getUsername());
        self::assertNull(Uri::new('example://user@host/path?query')->withUsername(null)->getUsername());
    }

    #[Test]
    public function it_can_update_the_password_component(): void
    {
        self::assertNull(Uri::new('example://user:pass@host/path?query')->withPassword(null)->getPassword());

        self::assertSame(
            'example://user:pass@host/path?query',
            Uri::new('example://user@host/path?query')->withPassword('pass')->toString()
        );
    }

    #[Test]
    public function it_requires_a_user_component_to_update_the_password_component(): void
    {
        $uri = Uri::new('example://host/path?query')->withPassword('pass');

        self::assertSame('pass', $uri->getPassword());
        self::assertNull($uri->getUsername());
        self::assertSame(':pass', $uri->getUserInfo());
    }

    #[Test]
    public function it_can_be_serialized_by_php(): void
    {
        $uri = Uri::new('https://user:pass@example.com:81/path?query#fragment');
        /** @var Uri $newUri */
        $newUri = unserialize(serialize($uri));

        self::assertTrue($uri->equals($newUri, UriComparisonMode::IncludeFragment));
    }

    #[Test]
    public function it_can_save_data_uri_binary_encoded(): void
    {
        $newFilePath = $this->rootPath.'/temp.gif';
        $uri = Uri::fromFileContents($this->rootPath.'/red-nose.gif');
        $uri->toFileContents($newFilePath);

        self::assertSame($uri->toString(), Uri::fromFileContents($newFilePath)->toString());

        // Ensure file handle of \SplFileObject gets closed.
        unlink($newFilePath);
    }

    #[Test]
    public function it_can_save_to_file_with_raw_data(): void
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\nCookie: foo=bar\r\n",
            ],
        ]);

        $newFilePath = $this->rootPath.'/temp.txt';

        $uri = Uri::fromFileContents($this->rootPath.'/hello-world.txt', $context);
        $uri->toFileContents($newFilePath);
        self::assertSame((string) $uri, (string) Uri::fromFileContents($newFilePath));

        $data = file_get_contents($newFilePath);
        self::assertStringContainsString(base64_encode((string) $data), $uri->getPath());

        // Ensure \SplFileObject is closed
        unlink($newFilePath);
    }

    public function test_it_can_update_the_user_info_when_no_user_component_is_present(): void
    {
        self::assertSame(
            Uri::new('https://user:pass@example.com')->withUserInfo(null, 'pass')->toString(),
            Uri::new('https://:pass@example.com')->toString()
        );
    }

    public function test_it_can_return_a_unicode_string_for_the_uri(): void
    {
        $uri = Uri::new('https://bébé.be');

        self::assertSame('xn--bb-bjab.be', $uri->getHost());
        self::assertSame('bébé.be', $uri->getUnicodeHost());
        self::assertSame('https://xn--bb-bjab.be', $uri->toAsciiString());
        self::assertSame('https://bébé.be', $uri->toUnicodeString());
    }

    #[DataProvider('provideValidMailtoUri')]
    public function test_it_can_validate_mailto_uri(string $uri): void
    {
        self::assertInstanceOf(Uri::class, Uri::parse($uri));
    }

    public static function provideValidMailtoUri(): iterable
    {
        yield 'basic email' => ['uri' => 'mailto:me@thephpleague.com'];
        yield 'basic email with subject' => ['uri' => 'mailto:me@thephpleague.com?subject=Hello'];
        yield 'basic email with body' => ['uri' => 'mailto:infobot@example.com?body=send%20current-issue'];
        yield 'request to subscribe to a mailing list' => ['uri' => 'mailto:majordomo@example.com?body=subscribe%20bamboo-l'];
        yield 'email including a cc' => ['uri' => 'mailto:joe@example.com?cc=bob@example.com&body=hello'];
        yield 'email without path but a to query string name' => ['uri' => 'mailto:?to=bob@example.com&body=hello'];
        yield 'email without path but a to query string name case insensitive' => ['uri' => 'mailto:?To=bob@example.com&body=hello'];
        yield 'complex email are also supported' => ['uri' => "mailto:%22%5C%5C%5C%22it's%5C%20ugly%5C%5C%5C%22%22@example.org"];
    }

    #[DataProvider('provideInvalidMailtoUri')]
    public function test_it_can_not_validate_mailto_uri(string $uri): void
    {
        self::assertNull(Uri::parse($uri));
    }

    public static function provideInvalidMailtoUri(): iterable
    {
        yield 'path does not contain a valid email' => ['uri' => 'mailto:joe'];
        yield 'no path and no to query' => ['uri' => 'mailto:?subject=Hello'];
        yield 'a valid email is missing with the to parameter' => ['uri' => 'mailto:?to=Hello'];
        yield 'email query can not contains the "?" character' => ['uri' => 'mailto:joe@example.com?cc=bob@example.com?body=hello'];
    }

    public function test_it_can_edit_a_mailto_uri(): void
    {
        $uri = Uri::new('?Reply-To=me@example.com')
            ->withPath('you@example.com')
            ->withScheme('mailto')
            ->toString();

        self::assertSame('mailto:you@example.com?Reply-To=me@example.com', $uri);
    }

    public function test_it_fails_to_edit_a_mailto_uri_in_the_wrong_order(): void
    {
        $this->expectException(SyntaxError::class);

        Uri::new('?Reply-To=me@example.com')->withScheme('mailto');
    }

    #[DataProvider('provideHostType')]
    public function test_it_can_tell_its_host_type(?string $host, HostType $hostType): void
    {
        $uri = Uri::fromComponents(['host' => $host]);

        self::assertSame(HostType::Ipv4 === $hostType, $uri->isIpv4Host());
        self::assertSame(HostType::Ipv6 === $hostType, $uri->isIpv6Host());
        self::assertSame(HostType::IpvFuture === $hostType, $uri->isIpvFutureHost());
        self::assertSame(HostType::RegisteredName === $hostType, $uri->isRegisteredNameHost());
    }

    public static function provideHostType(): iterable
    {
        yield 'host is IPv4' => [
            'host' => '192.168.2.1',
            'hostType' => HostType::Ipv4,
        ];

        yield 'host is IPv6' => [
            'host' => '[::1]',
            'hostType' => HostType::Ipv6,
        ];

        yield 'host is IPvFuture' => [
            'host' => '[v8.1.2.3]',
            'hostType' => HostType::IpvFuture,
        ];

        yield 'host is registered name' => [
            'host' => '_afdsaf.dsafsd',
            'hostType' => HostType::RegisteredName,
        ];

        yield 'host is a domain name which is a registered name' => [
            'host' => 'uri.thephpleague.com',
            'hostType' => HostType::RegisteredName,
        ];
    }

    public function test_it_can_tell_the_difference_between_a_registered_name_and_a_domain_name(): void
    {
        $uri = Uri::fromComponents(['host' => '_afdsaf.dsafsd']);

        self::assertTrue($uri->isRegisteredNameHost());
        self::assertFalse($uri->isDomainHost());

        $uri = Uri::fromComponents(['host' => 'uri.thephpleague.com']);

        self::assertTrue($uri->isRegisteredNameHost());
        self::assertTrue($uri->isDomainHost());
    }
}

<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Uri;

use finfo;
use League\Uri\Contracts\UriComponentInterface;
use League\Uri\Contracts\UriInterface;
use League\Uri\Exceptions\FileinfoSupportMissing;
use League\Uri\Exceptions\IdnaConversionFailed;
use League\Uri\Exceptions\IdnSupportMissing;
use League\Uri\Exceptions\SyntaxError;
use League\Uri\Idna\Idna;
use Psr\Http\Message\UriInterface as Psr7UriInterface;
use SensitiveParameter;
use Stringable;
use function array_filter;
use function array_key_first;
use function array_map;
use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function file_get_contents;
use function filter_var;
use function implode;
use function in_array;
use function inet_pton;
use function ltrim;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function rawurlencode;
use function str_contains;
use function str_replace;
use function strlen;
use function strpos;
use function strspn;
use function strtolower;
use function substr;
use const FILEINFO_MIME;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_IP;

/**
 * @phpstan-import-type InputComponentMap from UriString
 */
final class Uri implements UriInterface
{
    /**
     * RFC3986 invalid characters.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-2.2
     *
     * @var string
     */
    private const REGEXP_INVALID_CHARS = '/[\x00-\x1f\x7f]/';

    /**
     * RFC3986 Sub delimiter characters regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-2.2
     *
     * @var string
     */
    private const REGEXP_CHARS_SUBDELIM = "\!\$&'\(\)\*\+,;\=%";

    /**
     * RFC3986 unreserved characters regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-2.3
     *
     * @var string
     */
    private const REGEXP_CHARS_UNRESERVED = 'A-Za-z\d_\-\.~';

    /**
     * RFC3986 schema regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-3.1
     *
     * @var string
     */
    private const REGEXP_SCHEME = ',^[a-z]([-a-z\d+.]+)?$,i';

    /**
     * RFC3986 host identified by a registered name regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @var string
     */
    private const REGEXP_HOST_REGNAME = '/^(
        (?<unreserved>[a-z\d_~\-\.])|
        (?<sub_delims>[!$&\'()*+,;=])|
        (?<encoded>%[A-F\d]{2})
    )+$/x';

    /**
     * RFC3986 delimiters of the generic URI components regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-2.2
     *
     * @var string
     */
    private const REGEXP_HOST_GEN_DELIMS = '/[:\/?#\[\]@ ]/'; // Also includes space.

    /**
     * RFC3986 IPvFuture regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @var string
     */
    private const REGEXP_HOST_IPFUTURE = '/^
        v(?<version>[A-F\d])+\.
        (?:
            (?<unreserved>[a-z\d_~\-\.])|
            (?<sub_delims>[!$&\'()*+,;=:])  # also include the : character
        )+
    $/ix';

    /**
     * RFC3986 IPvFuture host and port component.
     *
     * @var string
     */
    private const REGEXP_HOST_PORT = ',^(?<host>(\[.*]|[^:])*)(:(?<port>[^/?#]*))?$,x';

    /**
     * Significant 10 bits of IP to detect Zone ID regular expression pattern.
     *
     * @var string
     */
    private const HOST_ADDRESS_BLOCK = "\xfe\x80";

    /**
     * Regular expression pattern to for file URI.
     * <volume> contains the volume but not the volume separator.
     * The volume separator may be URL-encoded (`|` as `%7C`) by ::formatPath(),
     * so we account for that here.
     *
     * @var string
     */
    private const REGEXP_FILE_PATH = ',^(?<delim>/)?(?<volume>[a-zA-Z])(?:[:|\|]|%7C)(?<rest>.*)?,';

    /**
     * Mimetype regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc2397
     *
     * @var string
     */
    private const REGEXP_MIMETYPE = ',^\w+/[-.\w]+(?:\+[-.\w]+)?$,';

    /**
     * Base64 content regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc2397
     *
     * @var string
     */
    private const REGEXP_BINARY = ',(;|^)base64$,';

    /**
     * Windows file path string regular expression pattern.
     * <root> contains both the volume and volume separator.
     *
     * @var string
     */
    private const REGEXP_WINDOW_PATH = ',^(?<root>[a-zA-Z][:|\|]),';

    /**
     * Supported schemes and corresponding default port.
     *
     * @var array<string, int|null>
     */
    private const SCHEME_DEFAULT_PORT = [
        'data' => null,
        'file' => null,
        'ftp' => 21,
        'gopher' => 70,
        'http' => 80,
        'https' => 443,
        'ws' => 80,
        'wss' => 443,
    ];

    /**
     * Maximum number of formatted host cached.
     *
     * @var int
     */
    private const MAXIMUM_FORMATTED_HOST_CACHED = 100;

    /**
     * All ASCII letters sorted by typical frequency of occurrence.
     *
     * @var string
     */
    private const ASCII = "\x20\x65\x69\x61\x73\x6E\x74\x72\x6F\x6C\x75\x64\x5D\x5B\x63\x6D\x70\x27\x0A\x67\x7C\x68\x76\x2E\x66\x62\x2C\x3A\x3D\x2D\x71\x31\x30\x43\x32\x2A\x79\x78\x29\x28\x4C\x39\x41\x53\x2F\x50\x22\x45\x6A\x4D\x49\x6B\x33\x3E\x35\x54\x3C\x44\x34\x7D\x42\x7B\x38\x46\x77\x52\x36\x37\x55\x47\x4E\x3B\x4A\x7A\x56\x23\x48\x4F\x57\x5F\x26\x21\x4B\x3F\x58\x51\x25\x59\x5C\x09\x5A\x2B\x7E\x5E\x24\x40\x60\x7F\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";

    /** @readonly */
    private ?string $scheme;
    /** @readonly */
    private ?string $userInfo;
    /** @readonly */
    private ?string $host;
    /** @readonly */
    private ?int $port;
    /** @readonly */
    private ?string $authority;
    /** @readonly */
    private string $path;
    /** @readonly */
    private ?string $query;
    /** @readonly */
    private ?string $fragment;
    private ?string $uri;

    private function __construct(
        ?string $scheme,
        ?string $user,
        #[SensitiveParameter] ?string $pass,
        ?string $host,
        ?int $port,
        string $path,
        ?string $query,
        ?string $fragment
    ) {
        $this->scheme = $this->formatScheme($scheme);
        $this->userInfo = $this->formatUserInfo($user, $pass);
        $this->host = $this->formatHost($host);
        $this->port = $this->formatPort($port);
        $this->authority = $this->setAuthority();
        $this->path = $this->formatPath($path);
        $this->query = $this->formatQueryAndFragment($query);
        $this->fragment = $this->formatQueryAndFragment($fragment);

        $this->assertValidState();
    }

    /**
     * Format the Scheme and Host component.
     *
     * @throws SyntaxError if the scheme is invalid
     */
    private function formatScheme(?string $scheme): ?string
    {
        if (null === $scheme || array_key_exists($scheme, self::SCHEME_DEFAULT_PORT)) {
            return $scheme;
        }

        $formattedScheme = strtolower($scheme);
        if (array_key_exists($formattedScheme, self::SCHEME_DEFAULT_PORT) || 1 === preg_match(self::REGEXP_SCHEME, $formattedScheme)) {
            return $formattedScheme;
        }

        throw new SyntaxError('The scheme `'.$scheme.'` is invalid.');
    }

    /**
     * Set the UserInfo component.
     */
    private function formatUserInfo(
        ?string $user,
        #[SensitiveParameter] ?string $password
    ): ?string {
        if (null === $user) {
            return null;
        }

        static $userPattern = '/[^%'.self::REGEXP_CHARS_UNRESERVED.self::REGEXP_CHARS_SUBDELIM.']++|%(?![A-Fa-f\d]{2})/';
        $user = preg_replace_callback($userPattern, Uri::urlEncodeMatch(...), $user);
        if (null === $password) {
            return $user;
        }

        static $passwordPattern = '/[^%:'.self::REGEXP_CHARS_UNRESERVED.self::REGEXP_CHARS_SUBDELIM.']++|%(?![A-Fa-f\d]{2})/';

        return $user.':'.preg_replace_callback($passwordPattern, Uri::urlEncodeMatch(...), $password);
    }

    /**
     * Returns the RFC3986 encoded string matched.
     */
    private static function urlEncodeMatch(array $matches): string
    {
        return rawurlencode($matches[0]);
    }

    /**
     * Validate and Format the Host component.
     */
    private function formatHost(?string $host): ?string
    {
        if (null === $host || '' === $host) {
            return $host;
        }

        static $formattedHostCache = [];
        if (isset($formattedHostCache[$host])) {
            return $formattedHostCache[$host];
        }

        $formattedHost = '[' === $host[0] ? $this->formatIp($host) : $this->formatRegisteredName($host);
        $formattedHostCache[$host] = $formattedHost;
        if (self::MAXIMUM_FORMATTED_HOST_CACHED < count($formattedHostCache)) {
            unset($formattedHostCache[array_key_first($formattedHostCache)]);
        }

        return $formattedHost;
    }

    /**
     * Validate and format a registered name.
     *
     * The host is converted to its ascii representation if needed
     *
     * @throws IdnSupportMissing if the submitted host required missing or misconfigured IDN support
     * @throws SyntaxError       if the submitted host is not a valid registered name
     */
    private function formatRegisteredName(string $host): string
    {
        $formattedHost = rawurldecode($host);
        if (1 === preg_match(self::REGEXP_HOST_REGNAME, $formattedHost)) {
            return $formattedHost;
        }

        if (1 === preg_match(self::REGEXP_HOST_GEN_DELIMS, $formattedHost)) {
            throw new SyntaxError('The host `'.$host.'` is invalid : a registered name can not contain URI delimiters or spaces.');
        }

        $info = Idna::toAscii($host, Idna::IDNA2008_ASCII);
        if (0 !== $info->errors()) {
            throw IdnaConversionFailed::dueToIDNAError($host, $info);
        }

        return $info->result();
    }

    /**
     * Validate and Format the IPv6/IPvfuture host.
     *
     * @throws SyntaxError if the submitted host is not a valid IP host
     */
    private function formatIp(string $host): string
    {
        $ip = substr($host, 1, -1);
        if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $host;
        }

        if (1 === preg_match(self::REGEXP_HOST_IPFUTURE, $ip, $matches) && !in_array($matches['version'], ['4', '6'], true)) {
            return $host;
        }

        $pos = strpos($ip, '%');
        if (false === $pos) {
            throw new SyntaxError('The host `'.$host.'` is invalid : the IP host is malformed.');
        }

        if (1 === preg_match(self::REGEXP_HOST_GEN_DELIMS, rawurldecode(substr($ip, $pos)))) {
            throw new SyntaxError('The host `'.$host.'` is invalid : the IP host is malformed.');
        }

        $ip = substr($ip, 0, $pos);
        if (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new SyntaxError('The host `'.$host.'` is invalid : the IP host is malformed.');
        }

        //Only the address block fe80::/10 can have a Zone ID attach to
        //let's detect the link local significant 10 bits
        if (str_starts_with((string)inet_pton($ip), self::HOST_ADDRESS_BLOCK)) {
            return $host;
        }

        throw new SyntaxError('The host `'.$host.'` is invalid : the IP host is malformed.');
    }

    /**
     * Format the Port component.
     *
     * @throws SyntaxError
     */
    private function formatPort(int|null $port = null): ?int
    {
        $defaultPort = self::SCHEME_DEFAULT_PORT[$this->scheme] ?? null;

        return match (true) {
            null === $port, $defaultPort === $port => null,
            0 > $port => throw new SyntaxError('The port `'.$port.'` is invalid.'),
            default => $port,
        };
    }

    /**
     * Create a new instance from a string.
     */
    public static function new(Stringable|string $uri = ''): self
    {
        $components = UriString::parse($uri);

        return new self(
            $components['scheme'],
            $components['user'],
            $components['pass'],
            $components['host'],
            $components['port'],
            $components['path'],
            $components['query'],
            $components['fragment']
        );
    }

    /**
     * Creates a new instance from a URI and a Base URI.
     *
     * The returned URI must be absolute.
     */
    public static function fromBaseUri(Stringable|String $uri, Stringable|String|null $baseUri = null): UriInterface
    {
        if (!$uri instanceof UriInterface) {
            $uri = self::new($uri);
        }

        if (null === $baseUri) {
            if (null === $uri->getScheme()) {
                throw new SyntaxError('the URI `'.$uri.'` must be absolute.');
            }

            if (null === $uri->getAuthority()) {
                return $uri;
            }

            /** @var UriInterface $uri */
            $uri = BaseUri::new($uri->withFragment(null)->withQuery(null)->withPath(''))->resolve($uri);

            return $uri;
        }

        if (!$baseUri instanceof BaseUri) {
            $baseUri = BaseUri::new($baseUri);
        }

        if (null === $baseUri->value->getScheme()) {
            throw new SyntaxError('the base URI `'.$baseUri->value.'` must be absolute.');
        }

        /** @var UriInterface $uri */
        $uri = $baseUri->resolve($uri);

        return $uri;
    }

    /**
     * Creates a new instance from a template.
     */
    public static function fromTemplate(Stringable|string $template, iterable $variables = []): self
    {
        return self::new(
            UriTemplate\Template::new($template)->expand($variables)
        );
    }

    /**
     * Create a new instance from a hash representation of the URI similar
     * to PHP parse_url function result.
     *
     * @param InputComponentMap $components a hash representation of the URI similar to PHP parse_url function result
     */
    public static function fromComponents(array $components = []): self
    {
        $components += [
            'scheme' => null, 'user' => null, 'pass' => null, 'host' => null,
            'port' => null, 'path' => '', 'query' => null, 'fragment' => null,
        ];

        if (null === $components['path']) {
            $components['path'] = '';
        }

        return new self(
            $components['scheme'],
            $components['user'],
            $components['pass'],
            $components['host'],
            $components['port'],
            $components['path'],
            $components['query'],
            $components['fragment']
        );
    }

    /**
     * Create a new instance from a data file path.
     *
     * @param resource|null $context
     *
     * @throws FileinfoSupportMissing If ext/fileinfo is not installed
     * @throws SyntaxError            If the file does not exist or is not readable
     */
    public static function fromFileContents(Stringable|string $path, $context = null): self
    {
        static $finfoSupport = null;
        $finfoSupport = $finfoSupport ?? class_exists(finfo::class);

        // @codeCoverageIgnoreStart
        if (!$finfoSupport) {
            throw new FileinfoSupportMissing('Please install ext/fileinfo to use the '.__METHOD__.'() method.');
        }
        // @codeCoverageIgnoreEnd

        $path = (string) $path;
        $fileArguments = [$path, false];
        $mimeArguments = [$path, FILEINFO_MIME];
        if (null !== $context) {
            $fileArguments[] = $context;
            $mimeArguments[] = $context;
        }

        set_error_handler(fn (int $errno, string $errstr, string $errfile, int $errline) => true);
        $raw = file_get_contents(...$fileArguments);
        restore_error_handler();

        if (false === $raw) {
            throw new SyntaxError('The file `'.$path.'` does not exist or is not readable.');
        }

        $mimetype = (string) (new finfo(FILEINFO_MIME))->file(...$mimeArguments);

        return Uri::fromComponents([
            'scheme' => 'data',
            'path' => str_replace(' ', '', $mimetype.';base64,'.base64_encode($raw)),
        ]);
    }

    /**
     * Create a new instance from a Unix path string.
     */
    public static function fromUnixPath(Stringable|string $path): self
    {
        $path = implode('/', array_map(rawurlencode(...), explode('/', (string) $path)));
        if ('/' !== ($path[0] ?? '')) {
            return Uri::fromComponents(['path' => $path]);
        }

        return Uri::fromComponents(['path' => $path, 'scheme' => 'file', 'host' => '']);
    }

    /**
     * Create a new instance from a local Windows path string.
     */
    public static function fromWindowsPath(Stringable|string $path): self
    {
        $path = (string) $path;
        $root = '';
        if (1 === preg_match(self::REGEXP_WINDOW_PATH, $path, $matches)) {
            $root = substr($matches['root'], 0, -1).':';
            $path = substr($path, strlen($root));
        }
        $path = str_replace('\\', '/', $path);
        $path = implode('/', array_map(rawurlencode(...), explode('/', $path)));

        //Local Windows absolute path
        if ('' !== $root) {
            return Uri::fromComponents(['path' => '/'.$root.$path, 'scheme' => 'file', 'host' => '']);
        }

        //UNC Windows Path
        if (!str_starts_with($path, '//')) {
            return Uri::fromComponents(['path' => $path]);
        }

        $parts = explode('/', substr($path, 2), 2) + [1 => null];

        return Uri::fromComponents(['host' => $parts[0], 'path' => '/'.$parts[1], 'scheme' => 'file']);
    }

    /**
     * Create a new instance from the environment.
     */
    public static function fromServer(array $server): self
    {
        $components = ['scheme' => self::fetchScheme($server)];
        [$components['user'], $components['pass']] = self::fetchUserInfo($server);
        [$components['host'], $components['port']] = self::fetchHostname($server);
        [$components['path'], $components['query']] = self::fetchRequestUri($server);

        return Uri::fromComponents($components);
    }

    /**
     * Returns the environment scheme.
     */
    private static function fetchScheme(array $server): string
    {
        $server += ['HTTPS' => ''];

        return match (true) {
            false !== filter_var($server['HTTPS'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) => 'https',
            default => 'http',
        };
    }

    /**
     * Returns the environment user info.
     *
     * @return non-empty-array{0: ?string, 1: ?string}
     */
    private static function fetchUserInfo(array $server): array
    {
        $server += ['PHP_AUTH_USER' => null, 'PHP_AUTH_PW' => null, 'HTTP_AUTHORIZATION' => ''];
        $user = $server['PHP_AUTH_USER'];
        $pass = $server['PHP_AUTH_PW'];
        if (str_starts_with(strtolower($server['HTTP_AUTHORIZATION']), 'basic')) {
            $userinfo = base64_decode(substr($server['HTTP_AUTHORIZATION'], 6), true);
            if (false === $userinfo) {
                throw new SyntaxError('The user info could not be detected');
            }
            [$user, $pass] = explode(':', $userinfo, 2) + [1 => null];
        }

        if (null !== $user) {
            $user = rawurlencode($user);
        }

        if (null !== $pass) {
            $pass = rawurlencode($pass);
        }

        return [$user, $pass];
    }

    /**
     * Returns the environment host.
     *
     * @throws SyntaxError If the host can not be detected
     *
     * @return array{0:string|null, 1:int|null}
     */
    private static function fetchHostname(array $server): array
    {
        $server += ['SERVER_PORT' => null];
        if (null !== $server['SERVER_PORT']) {
            $server['SERVER_PORT'] = (int) $server['SERVER_PORT'];
        }

        if (isset($server['HTTP_HOST']) && 1 === preg_match(self::REGEXP_HOST_PORT, $server['HTTP_HOST'], $matches)) {
            $matches += ['host' => null, 'port' => null];
            if (null !== $matches['port']) {
                $matches['port'] = (int) $matches['port'];
            }

            if (null !== $matches['host']) {
                $matches['host'] = (string) $matches['host'];
            }

            return [
                $matches['host'],
                $matches['port'] ?? $server['SERVER_PORT'],
            ];
        }

        if (!isset($server['SERVER_ADDR'])) {
            throw new SyntaxError('The host could not be detected');
        }

        if (false === filter_var($server['SERVER_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $server['SERVER_ADDR'] = '['.$server['SERVER_ADDR'].']';
        }

        return [$server['SERVER_ADDR'], $server['SERVER_PORT']];
    }

    /**
     * Returns the environment path.
     *
     * @return non-empty-array{0:?string, 1:?string}
     */
    private static function fetchRequestUri(array $server): array
    {
        $server += ['IIS_WasUrlRewritten' => null, 'UNENCODED_URL' => '', 'PHP_SELF' => '', 'QUERY_STRING' => null];
        if ('1' === $server['IIS_WasUrlRewritten'] && '' !== $server['UNENCODED_URL']) {
            return explode('?', $server['UNENCODED_URL'], 2) + [1 => null];
        }

        if (isset($server['REQUEST_URI'])) {
            [$path] = explode('?', $server['REQUEST_URI'], 2);
            $query = ('' !== $server['QUERY_STRING']) ? $server['QUERY_STRING'] : null;

            return [$path, $query];
        }

        return [$server['PHP_SELF'], $server['QUERY_STRING']];
    }

    /**
     * Generate the URI authority part.
     */
    private function setAuthority(): ?string
    {
        $authority = null;
        if (null !== $this->userInfo) {
            $authority = $this->userInfo.'@';
        }

        if (null !== $this->host) {
            $authority .= $this->host;
        }

        if (null !== $this->port) {
            $authority .= ':'.$this->port;
        }

        return $authority;
    }

    /**
     * Format the Path component.
     */
    private function formatPath(string $path): string
    {
        if ('data' === $this->scheme) {
            $path = $this->formatDataPath($path);
        }

        if ('/' !== $path) {
            static $pattern = '/[^'.self::REGEXP_CHARS_UNRESERVED.self::REGEXP_CHARS_SUBDELIM.':@\/}{]++|%(?![A-Fa-f\d]{2})/';

            $path = (string) preg_replace_callback($pattern, Uri::urlEncodeMatch(...), $path);
        }

        if ('file' === $this->scheme) {
            $path = $this->formatFilePath($path);
        }

        return $path;
    }

    /**
     * Filter the Path component.
     *
     * @link https://tools.ietf.org/html/rfc2397
     *
     * @throws SyntaxError If the path is not compliant with RFC2397
     */
    private function formatDataPath(string $path): string
    {
        if ('' == $path) {
            return 'text/plain;charset=us-ascii,';
        }

        if (strlen($path) !== strspn($path, self::ASCII) || !str_contains($path, ',')) {
            throw new SyntaxError('The path `'.$path.'` is invalid according to RFC2937.');
        }

        $parts = explode(',', $path, 2) + [1 => null];
        $mediatype = explode(';', (string) $parts[0], 2) + [1 => null];
        $data = (string) $parts[1];
        $mimetype = $mediatype[0];
        if (null === $mimetype || '' === $mimetype) {
            $mimetype = 'text/plain';
        }

        $parameters = $mediatype[1];
        if (null === $parameters || '' === $parameters) {
            $parameters = 'charset=us-ascii';
        }

        $this->assertValidPath($mimetype, $parameters, $data);

        return $mimetype.';'.$parameters.','.$data;
    }

    /**
     * Assert the path is a compliant with RFC2397.
     *
     * @link https://tools.ietf.org/html/rfc2397
     *
     * @throws SyntaxError If the mediatype or the data are not compliant with the RFC2397
     */
    private function assertValidPath(string $mimetype, string $parameters, string $data): void
    {
        if (1 !== preg_match(self::REGEXP_MIMETYPE, $mimetype)) {
            throw new SyntaxError('The path mimetype `'.$mimetype.'` is invalid.');
        }

        $isBinary = 1 === preg_match(self::REGEXP_BINARY, $parameters, $matches);
        if ($isBinary) {
            $parameters = substr($parameters, 0, - strlen($matches[0]));
        }

        $res = array_filter(array_filter(explode(';', $parameters), $this->validateParameter(...)));
        if ([] !== $res) {
            throw new SyntaxError('The path paremeters `'.$parameters.'` is invalid.');
        }

        if (!$isBinary) {
            return;
        }

        $res = base64_decode($data, true);
        if (false === $res || $data !== base64_encode($res)) {
            throw new SyntaxError('The path data `'.$data.'` is invalid.');
        }
    }

    /**
     * Validate mediatype parameter.
     */
    private function validateParameter(string $parameter): bool
    {
        $properties = explode('=', $parameter);

        return 2 != count($properties) || 'base64' === strtolower($properties[0]);
    }

    /**
     * Format path component for file scheme.
     */
    private function formatFilePath(string $path): string
    {
        return (string) preg_replace_callback(
            self::REGEXP_FILE_PATH,
            static fn (array $matches): string => $matches['delim'].$matches['volume'].':'.$matches['rest'],
            $path
        );
    }

    /**
     * Format the Query or the Fragment component.
     *
     * Returns a array containing:
     * <ul>
     * <li> the formatted component (a string or null)</li>
     * <li> a boolean flag telling wether the delimiter is to be added to the component
     * when building the URI string representation</li>
     * </ul>
     */
    private function formatQueryAndFragment(?string $component): ?string
    {
        if (null === $component || '' === $component) {
            return $component;
        }

        static $pattern = '/[^'.self::REGEXP_CHARS_UNRESERVED.self::REGEXP_CHARS_SUBDELIM.':@\/?]++|%(?![A-Fa-f\d]{2})/';

        return preg_replace_callback($pattern, self::urlEncodeMatch(...), $component);
    }

    /**
     * assert the URI internal state is valid.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-3
     * @link https://tools.ietf.org/html/rfc3986#section-3.3
     *
     * @throws SyntaxError if the URI is in an invalid state according to RFC3986
     * @throws SyntaxError if the URI is in an invalid state according to scheme specific rules
     */
    private function assertValidState(): void
    {
        if (null !== $this->authority && ('' !== $this->path && '/' !== $this->path[0])) {
            throw new SyntaxError('If an authority is present the path must be empty or start with a `/`.');
        }

        if (null === $this->authority && str_starts_with($this->path, '//')) {
            throw new SyntaxError('If there is no authority the path `'.$this->path.'` can not start with a `//`.');
        }

        $pos = strpos($this->path, ':');
        if (null === $this->authority
            && null === $this->scheme
            && false !== $pos
            && !str_contains(substr($this->path, 0, $pos), '/')
        ) {
            throw new SyntaxError('In absence of a scheme and an authority the first path segment cannot contain a colon (":") character.');
        }

        $this->uri = null;

        if (! match ($this->scheme) {
            'data' => $this->isUriWithSchemeAndPathOnly(),
            'file' => $this->isUriWithSchemeHostAndPathOnly(),
            'ftp', 'gopher' => $this->isNonEmptyHostUriWithoutFragmentAndQuery(),
            'http', 'https' => $this->isNonEmptyHostUri(),
            'ws', 'wss' => $this->isNonEmptyHostUriWithoutFragment(),
            default => true,
        }) {
            throw new SyntaxError('The uri `'.$this.'` is invalid for the `'.$this->scheme.'` scheme.');
        }
    }

    /**
     * URI validation for URI schemes which allows only scheme and path components.
     */
    private function isUriWithSchemeAndPathOnly(): bool
    {
        return null === $this->authority
            && null === $this->query
            && null === $this->fragment;
    }

    /**
     * URI validation for URI schemes which allows only scheme, host and path components.
     */
    private function isUriWithSchemeHostAndPathOnly(): bool
    {
        return null === $this->userInfo
            && null === $this->port
            && null === $this->query
            && null === $this->fragment
            && !('' != $this->scheme && null === $this->host);
    }

    /**
     * URI validation for URI schemes which disallow the empty '' host.
     */
    private function isNonEmptyHostUri(): bool
    {
        return '' !== $this->host
            && !(null !== $this->scheme && null === $this->host);
    }

    /**
     * URI validation for URIs schemes which disallow the empty '' host
     * and forbids the fragment component.
     */
    private function isNonEmptyHostUriWithoutFragment(): bool
    {
        return $this->isNonEmptyHostUri() && null === $this->fragment;
    }

    /**
     * URI validation for URIs schemes which disallow the empty '' host
     * and forbids fragment and query components.
     */
    private function isNonEmptyHostUriWithoutFragmentAndQuery(): bool
    {
        return $this->isNonEmptyHostUri() && null === $this->fragment && null === $this->query;
    }

    /**
     * Generate the URI string representation from its components.
     *
     * @link https://tools.ietf.org/html/rfc3986#section-5.3
     */
    private function getUriString(
        ?string $scheme,
        ?string $authority,
        string $path,
        ?string $query,
        ?string $fragment
    ): string {
        if (null !== $scheme) {
            $scheme = $scheme.':';
        }

        if (null !== $authority) {
            $authority = '//'.$authority;
        }

        if (null !== $query) {
            $query = '?'.$query;
        }

        if (null !== $fragment) {
            $fragment = '#'.$fragment;
        }

        return $scheme.$authority.$path.$query.$fragment;
    }

    public function toString(): string
    {
        return $this->uri ??= $this->getUriString(
            $this->scheme,
            $this->authority,
            $this->path,
            $this->query,
            $this->fragment
        );
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * @return array{
     *     scheme:?string,
     *     user_info:?string,
     *     host:?string,
     *     port:?int,
     *     path:string,
     *     query:?string,
     *     fragment:?string
     * }
     */
    public function __debugInfo(): array
    {
        return [
            'scheme' => $this->scheme,
            'user_info' => isset($this->userInfo) ? preg_replace(',:(.*).?$,', ':***', $this->userInfo) : null,
            'host' => $this->host,
            'port' => $this->port,
            'path' => $this->path,
            'query' => $this->query,
            'fragment' => $this->fragment,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority(): ?string
    {
        return $this->authority;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo(): ?string
    {
        return $this->userInfo;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * {@inheritDoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        if (str_starts_with($this->path, '//')) {
            return '/'.ltrim($this->path, '/');
        }

        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme(Stringable|string|null $scheme): UriInterface
    {
        $scheme = $this->formatScheme($this->filterString($scheme));
        if ($scheme === $this->scheme) {
            return $this;
        }

        $clone = clone $this;
        $clone->scheme = $scheme;
        $clone->port = $clone->formatPort($clone->port);
        $clone->authority = $clone->setAuthority();
        $clone->assertValidState();

        return $clone;
    }

    /**
     * Filter a string.
     *
     * @throws SyntaxError if the submitted data can not be converted to string
     */
    private function filterString(Stringable|string|null $str): ?string
    {
        if ($str instanceof UriComponentInterface) {
            $str = $str->value();
        }

        if (null === $str) {
            return null;
        }

        $str = (string) $str;
        if (1 !== preg_match(self::REGEXP_INVALID_CHARS, $str)) {
            return $str;
        }

        throw new SyntaxError('The component `'.$str.'` contains invalid characters.');
    }

    public function withUserInfo(
        Stringable|string|null $user,
        #[SensitiveParameter] Stringable|string|null $password = null
    ): UriInterface {
        $user_info = null;
        $user = $this->filterString($user);
        if (null !== $password) {
            $password = $this->filterString($password);
        }

        if ('' !== $user) {
            $user_info = $this->formatUserInfo($user, $password);
        }

        if ($user_info === $this->userInfo) {
            return $this;
        }

        $clone = clone $this;
        $clone->userInfo = $user_info;
        $clone->authority = $clone->setAuthority();
        $clone->assertValidState();

        return $clone;
    }

    public function withHost(Stringable|string|null $host): UriInterface
    {
        $host = $this->formatHost($this->filterString($host));
        if ($host === $this->host) {
            return $this;
        }

        $clone = clone $this;
        $clone->host = $host;
        $clone->authority = $clone->setAuthority();
        $clone->assertValidState();

        return $clone;
    }

    public function withPort(int|null $port): UriInterface
    {
        $port = $this->formatPort($port);
        if ($port === $this->port) {
            return $this;
        }

        $clone = clone $this;
        $clone->port = $port;
        $clone->authority = $clone->setAuthority();
        $clone->assertValidState();

        return $clone;
    }

    public function withPath(Stringable|string $path): UriInterface
    {
        $path = $this->filterString($path);
        if (null === $path) {
            throw new SyntaxError('The path component can not be null.');
        }
        $path = $this->formatPath($path);
        if ($path === $this->path) {
            return $this;
        }

        $clone = clone $this;
        $clone->path = $path;
        $clone->assertValidState();

        return $clone;
    }

    public function withQuery(Stringable|string|null $query): UriInterface
    {
        $query = $this->formatQueryAndFragment($this->filterString($query));
        if ($query === $this->query) {
            return $this;
        }

        $clone = clone $this;
        $clone->query = $query;
        $clone->assertValidState();

        return $clone;
    }

    public function withFragment(Stringable|string|null $fragment): UriInterface
    {
        $fragment = $this->formatQueryAndFragment($this->filterString($fragment));
        if ($fragment === $this->fragment) {
            return $this;
        }

        $clone = clone $this;
        $clone->fragment = $fragment;
        $clone->assertValidState();

        return $clone;
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 9.9.0
     * @codeCoverageIgnore
     * @see Uri::new()
     */
    public static function createFromString(Stringable|string $uri = ''): self
    {
        return self::new($uri);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 9.9.0
     * @codeCoverageIgnore
     * @see Uri::fromComponents()
     *
     * @param InputComponentMap $components a hash representation of the URI similar to PHP parse_url function result
     */
    public static function createFromComponents(array $components = []): self
    {
        return self::fromComponents($components);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @param resource|null $context
     *
     * @throws FileinfoSupportMissing If ext/fileinfo is not installed
     * @throws SyntaxError            If the file does not exist or is not readable
     *@see Uri::fromFileContents()
     *
     * @deprecated Since version 9.9.0
     * @codeCoverageIgnore
     */
    public static function createFromDataPath(string $path, $context = null): self
    {
        return self::fromFileContents($path, $context);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     * @see Uri::fromBaseUri()
     *
     * Creates a new instance from a URI and a Base URI.
     *
     * The returned URI must be absolute.
     */
    public static function createFromBaseUri(
        Stringable|UriInterface|String $uri,
        Stringable|UriInterface|String|null $baseUri = null
    ): UriInterface {
        return self::fromBaseUri($uri, $baseUri);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     * @see Uri::fromUnixPath()
     *
     * Create a new instance from a Unix path string.
     */
    public static function createFromUnixPath(string $uri = ''): self
    {
        return self::fromUnixPath($uri);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     * @see Uri::fromWindowsPath()
     *
     * Create a new instance from a local Windows path string.
     */
    public static function createFromWindowsPath(string $uri = ''): self
    {
        return self::fromWindowsPath($uri);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     * @see Uri::new()
     *
     * Create a new instance from a URI object.
     */
    public static function createFromUri(Psr7UriInterface|UriInterface $uri): self
    {
        return self::new($uri);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     * @see Uri::fromServer()
     *
     * Create a new instance from the environment.
     */
    public static function createFromServer(array $server): self
    {
        return self::fromServer($server);
    }
}

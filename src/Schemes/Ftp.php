<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Schemes;

use InvalidArgumentException;
use League\Uri\Components\Fragment;
use League\Uri\Components\FtpPath;
use League\Uri\Components\Host;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo;
use League\Uri\Interfaces\Fragment as FragmentInterface;
use League\Uri\Interfaces\FtpPath as FtpPathInterface;
use League\Uri\Interfaces\Host as HostInterface;
use League\Uri\Interfaces\Port as PortInterface;
use League\Uri\Interfaces\Query as QueryInterface;
use League\Uri\Interfaces\Scheme as SchemeInterface;
use League\Uri\Interfaces\Uri;
use League\Uri\Interfaces\UserInfo as UserInfoInterface;
use League\Uri\Schemes\Generic\AbstractUri;
use League\Uri\UriParser;

/**
 * Value object representing FTP Uri.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 *
 * @property-read SchemeInterface    $scheme
 * @property-read UserInfoInterface  $userInfo
 * @property-read HostInterface      $host
 * @property-read PortInterface      $port
 * @property-read FtpPathInterface   $path
 * @property-read QueryInterface     $query
 * @property-read FragmentInterface  $fragment
 */
class Ftp extends AbstractUri implements Uri
{
    /**
     * {@inheritdoc}
     */
    protected static $supportedSchemes = [
        'ftp' => 21,
    ];

    /**
     * Create a new instance of URI
     *
     * @param SchemeInterface   $scheme
     * @param UserInfoInterface $userInfo
     * @param HostInterface     $host
     * @param PortInterface     $port
     * @param FtpPathInterface  $path
     * @param QueryInterface    $query
     * @param FragmentInterface $fragment
     */
    public function __construct(
        SchemeInterface $scheme,
        UserInfoInterface $userInfo,
        HostInterface $host,
        PortInterface $port,
        FtpPathInterface $path,
        QueryInterface $query,
        FragmentInterface $fragment
    ) {
        $this->scheme = $scheme;
        $this->userInfo = $userInfo;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
        $this->assertValidObject();
    }

    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @throws InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents((new UriParser())->parse($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components
     *
     * @throws InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromComponents(array $components)
    {
        $components = (new UriParser())->normalizeUriHash($components);

        return new static(
            new Scheme($components['scheme']),
            new UserInfo($components['user'], $components['pass']),
            new Host($components['host']),
            new Port($components['port']),
            new FtpPath($components['path']),
            new Query($components['query']),
            new Fragment($components['fragment'])
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        return empty($this->fragment->__toString().$this->query->__toString())
            && $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }
}

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
use League\Uri\Components\DataPath;
use League\Uri\Components\Fragment;
use League\Uri\Components\Host;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo;
use League\Uri\Interfaces\Components\DataPath as DataPathInterface;
use League\Uri\Interfaces\Components\Fragment as FragmentInterface;
use League\Uri\Interfaces\Components\Host as HostInterface;
use League\Uri\Interfaces\Components\Port as PortInterface;
use League\Uri\Interfaces\Components\Query as QueryInterface;
use League\Uri\Interfaces\Components\Scheme as SchemeInterface;
use League\Uri\Interfaces\Components\UserInfo as UserInfoInterface;
use League\Uri\Interfaces\Schemes\Uri;
use League\Uri\Schemes\Generic\AbstractUri;
use League\Uri\UriParser;

/**
 * Value object representing Data Uri.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 *
 * @property-read SchemeInterface   $scheme
 * @property-read UserInfoInterface $userInfo
 * @property-read HostInterface     $host
 * @property-read PortInterface     $port
 * @property-read DataPathInterface $path
 * @property-read QueryInterface    $query
 * @property-read FragmentInterface $fragment
 */
class Data extends AbstractUri
{
    /**
     * Create a new instance of URI
     *
     * @param SchemeInterface   $scheme
     * @param UserInfoInterface $userInfo
     * @param HostInterface     $host
     * @param PortInterface     $port
     * @param DataPathInterface $path
     * @param QueryInterface    $query
     * @param FragmentInterface $fragment
     */
    public function __construct(
        SchemeInterface $scheme,
        UserInfoInterface $userInfo,
        HostInterface $host,
        PortInterface $port,
        DataPathInterface $path,
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
            new DataPath($components['path']),
            new Query($components['query']),
            new Fragment($components['fragment'])
        );
    }

    /**
     * Create a new instance from a file path
     *
     * @param string $path
     *
     * @throws InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromPath($path)
    {
        return new static(
            new Scheme('data'),
            new UserInfo(),
            new Host(),
            new Port(),
            DataPath::createFromPath($path),
            new Query(),
            new Fragment()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        if ($this->scheme->getContent() !== 'data') {
            throw new InvalidArgumentException('The submitted scheme is invalid for the class '.get_class($this));
        }

        return $this->isValidGenericUri() && $this->__toString() === 'data:'.$this->path->getUriComponent();
    }
}

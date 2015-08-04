<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Schemes;

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

/**
 * Value object representing Data Uri.
 *
 * @package League.uri
 * @since   4.0.0
 */
class Data extends AbstractUri implements Uri
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
     * {@inheritdoc}
     */
    protected function isValid()
    {
        return parent::isValid() && $this->__toString() === 'data:'.$this->path->getUriComponent();
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->path->getMimeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->path->getParameters();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->path->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function isBinaryData()
    {
        return $this->path->isBinaryData();
    }

    /**
     * {@inheritdoc}
     */
    public function toBinary()
    {
        return $this->withProperty('path', $this->path->toBinary());
    }

    /**
     * {@inheritdoc}
     */
    public function toAscii()
    {
        return $this->withProperty('path', $this->path->toAscii());
    }

    /**
     * {@inheritdoc}
     */
    public function save($path, $mode = 'w')
    {
        return $this->path->save($path, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function withParameters($parameters)
    {
        return $this->withProperty('path', $this->path->withParameters($parameters));
    }

    /**
     * Create a new instance from a file path
     *
     * @param string $path
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
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
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components
     *
     * @throws \InvalidArgumentException If the URI can not be parsed
     *
     * @return static
     */
    public static function createFromComponents(array $components)
    {
        $components = static::formatComponents($components);
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
}

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

use League\Uri\Components;
use League\Uri\Interfaces;

/**
 * Value object representing Data Uri.
 *
 * @package League.uri
 * @since   4.0.0
 */
class Data extends Generic\AbstractUri implements Interfaces\Schemes\Uri
{
    /**
     * Create a new instance of URI
     *
     * @param Interfaces\Components\Scheme   $scheme
     * @param Interfaces\Components\UserInfo $userInfo
     * @param Interfaces\Components\Host     $host
     * @param Interfaces\Components\Port     $port
     * @param Interfaces\Components\DataPath $path
     * @param Interfaces\Components\Query    $query
     * @param Interfaces\Components\Fragment $fragment
     */
    public function __construct(
        Interfaces\Components\Scheme $scheme,
        Interfaces\Components\UserInfo $userInfo,
        Interfaces\Components\Host $host,
        Interfaces\Components\Port $port,
        Interfaces\Components\DataPath $path,
        Interfaces\Components\Query $query,
        Interfaces\Components\Fragment $fragment
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
        return $this->__toString() === 'data:'.$this->path->getUriComponent();
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
            new Components\Scheme('data'),
            new Components\UserInfo(),
            new Components\Host(),
            new Components\Port(),
            Components\DataPath::createFromPath($path),
            new Components\Query(),
            new Components\Fragment()
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
            new Components\Scheme($components['scheme']),
            new Components\UserInfo($components['user'], $components['pass']),
            new Components\Host($components['host']),
            new Components\Port($components['port']),
            new Components\DataPath($components['path']),
            new Components\Query($components['query']),
            new Components\Fragment($components['fragment'])
        );
    }
}

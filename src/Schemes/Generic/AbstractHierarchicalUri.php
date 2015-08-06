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
namespace League\Uri\Schemes\Generic;

use League\Uri\Components\Fragment;
use League\Uri\Components\Host;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo;
use League\Uri\Interfaces\Components\Fragment as FragmentInterface;
use League\Uri\Interfaces\Components\HierarchicalPath as HierarchicalPathInterface;
use League\Uri\Interfaces\Components\Host as HostInterface;
use League\Uri\Interfaces\Components\Port as PortInterface;
use League\Uri\Interfaces\Components\Query as QueryInterface;
use League\Uri\Interfaces\Components\Scheme as SchemeInterface;
use League\Uri\Interfaces\Components\UserInfo as UserInfoInterface;
use League\Uri\Interfaces\Schemes\HierarchicalUri;
use League\Uri\Interfaces\Schemes\Uri;

/**
 * Value object representing a Hierarchical URI.
 *
 * @package League.uri
 * @since   4.0.0
 *
 * @property-read SchemeInterface           $scheme
 * @property-read UserInfoInterface         $userInfo
 * @property-read HostInterface             $host
 * @property-read PortInterface             $port
 * @property-read HierarchicalPathInterface $path
 * @property-read QueryInterface            $query
 * @property-read FragmentInterface         $fragment
 */
abstract class AbstractHierarchicalUri extends AbstractUri implements HierarchicalUri
{
    use HierarchicalPathModifierTrait;

    /**
     * Create a new instance of URI
     *
     * @param SchemeInterface           $scheme
     * @param UserInfoInterface         $userInfo
     * @param HostInterface             $host
     * @param PortInterface             $port
     * @param HierarchicalPathInterface $path
     * @param QueryInterface            $query
     * @param FragmentInterface         $fragment
     */
    public function __construct(
        SchemeInterface $scheme,
        UserInfoInterface $userInfo,
        HostInterface $host,
        PortInterface $port,
        HierarchicalPathInterface $path,
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
     * Tell whether the Hierarchical URI is valid
     *
     * @return bool
     */
    protected function isValidHierarchicalUri()
    {
        if (!$this->isValidGenericUri()) {
            return false;
        }

        if ($this->scheme->isEmpty()) {
            return true;
        }

        if (!isset(static::$supportedSchemes[$this->scheme->__toString()])) {
            return false;
        }

        return !($this->host->isEmpty() && !empty($this->getSchemeSpecificPart()));
    }

    /**
     * {@inheritdoc}
     */
    public function relativize(Uri $relative)
    {
        if (!$relative instanceof HierarchicalUri) {
            return $relative;
        }

        if ($this->getScheme() !== $relative->getScheme()
            || $this->getAuthority() !== $relative->getAuthority()
        ) {
            return $relative;
        }

        return $relative
                ->withScheme('')->withUserInfo('')->withHost('')->withPort('')
                ->withPath($this->path->relativize($relative->path)->__toString());
    }
}

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

use League\Uri\Interfaces\Fragment as FragmentInterface;
use League\Uri\Interfaces\HierarchicalPath as HierarchicalPathInterface;
use League\Uri\Interfaces\Host as HostInterface;
use League\Uri\Interfaces\Port as PortInterface;
use League\Uri\Interfaces\Query as QueryInterface;
use League\Uri\Interfaces\Scheme as SchemeInterface;
use League\Uri\Interfaces\Uri;
use League\Uri\Interfaces\UserInfo as UserInfoInterface;
use League\Uri\Schemes\Generic\AbstractHierarchicalUri;

/**
 * Value object representing WS and WSS Uri.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
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
class Ws extends AbstractHierarchicalUri implements Uri
{
    /**
     * {@inheritdoc}
     */
    protected static $supportedSchemes = [
        'ws' => 80,
        'wss' => 443,
    ];

    /**
     * {@inheritdoc}
     */
    protected function isValid()
    {
        return empty($this->fragment->getContent())
            && $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }
}

<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url\Utilities;

use League\Url\Port;
use League\Url\Scheme;

/**
 * Trait to validate Url standard Port
 *
 * @package League.url
 * @since 4.0.0
 */
trait StandardPort
{
    /**
     * Standard ports for known schemes
     *
     * @var array
     */
    protected static $standardPorts = [
        'ftp'   => [21],
        'ftps'  => [989, 990],
        'https' => [443],
        'http'  => [80],
        'git'   => [22],
        'rsync' => [22, 873],
        'ssh'   => [22],
        'svn'   => [22],
        'ws'    => [80],
        'wss'   => [443],
    ];

    /**
     * Standard scheme for known ports
     *
     * @var array
     */
    protected static $standardSchemes = [
        21  => ['ftp'],
        22  => ['git', 'rsync', 'ssh', 'svn'],
        80  => ['http', 'ws'],
        443 => ['https', 'wss'],
        873 => ['rsync'],
        989 => ['ftps'],
        990 => ['ftps'],
    ];

    /**
     * Return Mapped Data
     *
     * @param callable    $callable a callable to apply to each found data
     * @param array       $arr      the array to look the index into
     * @param string|null $key      the index we are searching for
     *
     * @return League\Url\Interfaces\Component[]
     */
    protected function getAssociatedData(callable $callable, array $arr, $key)
    {
        $res = [];
        if (array_key_exists($key, $arr)) {
            $res = $arr[$key];
        }
        sort($res);

        return array_map(function ($value) use ($callable) {
            return $callable($value);
        }, $res);
    }

    /**
     * Return all the port attached to a given scheme
     *
     * @param string $scheme
     *
     * @return League\Url\Port[]
     */
    protected function getStandardPortsFromScheme($scheme)
    {
        return $this->getAssociatedData(function ($value) {
            return new Port($value);
        }, static::$standardPorts, $scheme);
    }

    /**
     * Return all the scheme attached to a given port
     *
     * @param null|int $port
     *
     * @return League\Url\Scheme[]
     */
    protected function getStandardSchemesFromPort($port)
    {
        return $this->getAssociatedData(function ($value) {
            return new Scheme($value);
        }, static::$standardSchemes, $port);
    }
}

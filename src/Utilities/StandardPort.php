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
        'ftp'    => [21],
        'ftps'   => [989, 990],
        'https'  => [443],
        'http'   => [80],
        'rsync'  => [22, 873],
        'ssh'    => [22],
        'ws'     => [80],
        'wss'    => [443],
        'gopher' => [70],
    ];

    /**
     * Standard scheme for known ports
     *
     * @var array
     */
    protected static $standardSchemes = [
        21  => ['ftp'],
        22  => ['rsync', 'ssh'],
        80  => ['http', 'ws'],
        443 => ['https', 'wss'],
        873 => ['rsync'],
        989 => ['ftps'],
        990 => ['ftps'],
        70  => ['gopher'],
    ];

    /**
     * Return Mapped Data
     *
     * @param array       $arr      the array to look the index into
     * @param string|null $key      the index we are searching for
     *
     * @return League\Url\Interfaces\Component[]
     */
    protected function getAssociatedData(array $arr, $key)
    {
        $res = [];
        if (array_key_exists($key, $arr)) {
            $res = $arr[$key];
        }
        sort($res);

        return $res;
    }
}

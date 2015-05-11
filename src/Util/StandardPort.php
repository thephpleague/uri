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
namespace League\Url\Util;

/**
* Trait to validate Url standard Port
*
* @package League.url
* @since 4.0.0
*/
trait StandardPort
{
    /**
     * Standard Port for known schemes
     *
     * @var array
     */
    protected static $standardPorts = [
        'ftp'   => [21  => 1],
        'ftps'  => [990 => 1, 989 => 1],
        'https' => [443 => 1],
        'http'  => [80  => 1],
        'ldap'  => [389 => 1],
        'ldaps' => [636 => 1],
        'ssh'   => [22  => 1],
        'ws'    => [80  => 1],
        'wss'   => [443 => 1],
    ];
}

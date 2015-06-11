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

/**
 * Trait to register Schemes and their correspondant Standard Port
 *
 * @package League.url
 * @since 4.0.0
 */
trait RegisteredSchemes
{
    /**
     * Standard ports for known schemes
     *
     * @var array
     */
    protected static $registeredSchemes = [
        'ftp'   => [21],
        'ftps'  => [989, 990],
        'https' => [443],
        'http'  => [80],
        'ws'    => [80],
        'wss'   => [443],
        'file'  => [],
    ];
}

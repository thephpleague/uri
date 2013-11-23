<?php
/**
* Bakame.url - A lightweight Url Parser library
*
* @author Ignace Nyamagana Butera <nyamsprod@gmail.com>
* @copyright 2013 Ignace Nyamagana Butera
* @link https://github.com/nyamsprod/Bakame.url
* @license http://opensource.org/licenses/MIT
* @version 1.0.0
* @package Bakame.url
*
* MIT LICENSE
*
* Permission is hereby granted, free of charge, to any person obtaining
* a copy of this software and associated documentation files (the
* "Software"), to deal in the Software without restriction, including
* without limitation the rights to use, copy, modify, merge, publish,
* distribute, sublicense, and/or sell copies of the Software, and to
* permit persons to whom the Software is furnished to do so, subject to
* the following conditions:
*
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
* LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
* OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
* WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
namespace Bakame\Url;

use InvalidArgumentException;
use Bakame\Url\Components\Scheme;
use Bakame\Url\Components\Auth;
use Bakame\Url\Components\Host;
use Bakame\Url\Components\Port;
use Bakame\Url\Components\Path;
use Bakame\Url\Components\Query;
use Bakame\Url\Components\Fragment;
use Bakame\Url\Components\Url;

/**
 *  A factory to ease the creation of a Bakame\Url\Url Object
 *
 * @package Bakame.Url
 *
 */
class Factory
{

    /**
     * default result from a parse_url execution without error
     * @var array
     */
    private static $components = array(
        'scheme' => null,
        'user' => null,
        'pass' => null,
        'host' => null,
        'port' => null,
        'path' => null,
        'query' => null,
        'fragment' => null,
    );

    /**
     * Return a instance of Bakame\Url\Url from a server array
     *
     * @param array $server the server array
     *
     * @return Bakame\Url\Url
     */
    public static function createUrlFromServer(array $server)
    {
        $protocol = '';
        if (isset($server['SERVER_PROTOCOL'])) {
            $protocol = explode('/', $server['SERVER_PROTOCOL']);
            $protocol = strtolower($protocol[0]);
            if (isset($server['HTTPS']) && 'off' != $server['HTTPS']) {
                $protocol .= 's';
            }
            $protocol .= ':';
        }
        $protocol .= '//';

        $host = $server['SERVER_ADDR'];
        if (isset($server['HTTP_HOST'])) {
            $host = $server['HTTP_HOST'];
        }

        $port = '';
        if (array_key_exists('SERVER_PORT', $server) && '80' != $server['SERVER_PORT']) {
            $port = ':'.$server['SERVER_PORT'];
        }

        $requestUri = $server['PHP_SELF'];
        if (isset($server['REQUEST_URI'])) {
            $requestUri = $server['REQUEST_URI'];
        }

        $url = array_merge(
            self::$components,
            parse_url($protocol.$host.$port.$requestUri)
        );

        return self::createUrl(
            $url['scheme'],
            $url['user'],
            $url['pass'],
            $url['host'],
            $url['port'],
            $url['path'],
            $url['query'],
            $url['fragment']
        );
    }

    /**
     * Return a instance of Bakame\Url\Url from a simple string
     *
     * @param string $url
     *
     * @return Bakame\Url\Url
     */
    public static function createUrlFromString($url)
    {
        $res = @parse_url($url);
        if (false === $res) {
            throw new InvalidArgumentException('Invalid URL given');
        }
        $url = array_merge(self::$components, $res);
        //FIX FOR PHP 5.4.7- BUGS
        if (null == $url['scheme'] && null == $url['host'] && 0 === strpos($url['path'], '//')) {
            $tmp = substr($url['path'], 2);
            list($url['host'], $url['path']) = explode('/', $tmp, 2);
            if (strpos($url['host'], '@')) {
                list($auth, $url['host']) = explode('@', $url['host']);
                $url['user'] = $auth;
                $url['pass'] = null;
                if (false !== strpos($auth, ':')) {
                    list($url['user'], $url['pass']) = explode(':', $auth);
                }
            }
        }

        return self::createUrl(
            $url['scheme'],
            $url['user'],
            $url['pass'],
            $url['host'],
            $url['port'],
            $url['path'],
            $url['query'],
            $url['fragment']
        );
    }

    /**
     * Create a new Instance of Bakame\Url\Url
     *
     * @param string $scheme   url scheme
     * @param string $user     url user
     * @param string $pass     url pass
     * @param string $host     url host
     * @param string $port     url port
     * @param string $path     url path
     * @param string $query    url query
     * @param string $fragment url fragment
     *
     * @return Bakame\Url\Url
     */
    public static function createUrl(
        $scheme = null,
        $user = null,
        $pass = null,
        $host = null,
        $port = null,
        $path = null,
        $query = null,
        $fragment = null
    ) {
        return new Url(
            new Scheme($scheme),
            new Auth($user, $pass),
            new Host($host),
            new Port($port),
            new Path($path),
            new Query($query),
            new Fragment($fragment)
        );
    }
}

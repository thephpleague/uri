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

class Factory
{

    /**
     * default result from a parse_url execution without error
     * @var array
     */
    private static $default_url = array(
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
    public static function createFromServer(array $server)
    {
        $requestUri = $server['PHP_SELF'];
        if (isset($server['REQUEST_URI'])) {
            $requestUri = $server['REQUEST_URI'];
        }
        $https = '';
        if (array_key_exists('HTTPS', $server) && 'on' == $server['HTTPS']) {
            $https = 's';
        }
        $protocol = explode('/', $server['SERVER_PROTOCOL']);
        $protocol = strtolower($protocol[0]).$https;
        $port = '';
        if (array_key_exists('SERVER_PORT', $server) && '80' != $server['SERVER_PORT']) {
            $port = ':'.$server['SERVER_PORT'];
        }

        $url = parse_url($protocol.'://'.$server['HTTP_HOST'].$port.$requestUri);
        $url = array_replace(self::$default_url, $url);

        return self::create(
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
    public static function createFromString($url)
    {
        $res = parse_url($url);
        if (false === $res) {
            throw new InvalidArgumentException('Invalid URL given');
        }
        $url = array_replace(self::$default_url, $res);
        //FIX FOR PHP 5.4.7- BUG
        if (null == $url['scheme'] && null == $url['host'] && 0 === strpos($url['path'], '//')) {
            $tmp = substr($url['path'], 2);
            list($url['host'], $url['path']) = explode('/', $tmp, 2);
        }

        return self::create(
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
    public static function create(
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
            new Segment($host, '.'),
            new Port($port),
            new Segment($path, '/'),
            new Query($query),
            new Fragment($fragment)
        );
    }
}

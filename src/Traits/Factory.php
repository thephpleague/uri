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
namespace League\Url\Traits;

use RuntimeException;

/**
 * A Factory trait to create new URLInterface object
 *
 *  @package League.url
 *  @since  4.0.0
 */
trait Factory
{
    /**
     * Return a instance of Url from a string
     *
     * @param string $url a string or an object that implement the __toString method
     *
     * @return static
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromUrl($url)
    {
        $url = (string) $url;
        $url = trim($url);
        $original_url = $url;
        $url = self::sanitizeUrl($url);

        //if no valid scheme is found we add one
        if (is_null($url)) {
            throw new RuntimeException(sprintf('The given URL: `%s` could not be parse', $original_url));
        }
        $components = @parse_url($url);
        if (false === $components) {
            throw new RuntimeException(sprintf('The given URL: `%s` could not be parse', $original_url));
        }

        return new static($components);
    }

    private static function sanitizeUrl($url)
    {
        if ('' == $url || strpos($url, '//') === 0) {
            return $url;
        } elseif (! preg_match(',^((http|ftp|ws)s?:),i', $url, $matches)) {
            return '//'.$url;
        }

        $scheme_length = strlen($matches[0]);
        if (strpos(substr($url, $scheme_length), '//') === 0) {
            return $url;
        }

        return null;
    }

    /**
     * Return a instance of Url from a server array
     *
     * @param array $server the server array
     *
     * @return static
     *
     * @throws RuntimeException If the URL can not be parse
     */
    public static function createFromServer(array $server)
    {
        $scheme = self::fetchServerScheme($server);
        $host = self::fetchServerHost($server);
        $port = self::fetchServerPort($server);
        $request = self::fetchServerRequestUri($server);

        return self::createFromUrl($scheme.$host.$port.$request);
    }

    /**
     * Return the Server URL scheme component
     *
     * @param array $server the server array
     *
     * @return string
     */
    private static function fetchServerScheme(array $server)
    {
        $scheme = '';
        if (isset($server['SERVER_PROTOCOL'])) {
            $scheme = explode('/', $server['SERVER_PROTOCOL']);
            $scheme = strtolower($scheme[0]);
            if (isset($server['HTTPS']) && 'off' != $server['HTTPS']) {
                $scheme .= 's';
            }
            $scheme .= ':';
        }

        return $scheme.'//';
    }

    /**
     * Return the Server URL host component
     *
     * @param array $server the server array
     *
     * @return string
     *
     * @throws \RuntimeException If no host is detected
     */
    private static function fetchServerHost(array $server)
    {
        if (isset($server['HTTP_HOST'])) {
            $header = $server['HTTP_HOST'];
            if (! preg_match('/(:\d+)$/', $header, $matches)) {
                return $header;
            }
            return substr($header, 0, -strlen($matches[1]));
        } elseif (isset($server['SERVER_ADDR'])) {
            return $server['SERVER_ADDR'];
        }

        throw new RuntimeException('Host could not be detected');
    }

    /**
     * Return the Server URL port component
     *
     * @param array $server the server array
     *
     * @return string
     */
    private static function fetchServerPort(array $server)
    {
        $port = '';
        if (isset($server['SERVER_PORT']) && '80' != $server['SERVER_PORT']) {
            $port = ':'. (int) $server['SERVER_PORT'];
        }

        return $port;
    }

    /**
     * Return the Server URL Request Uri component
     *
     * @param array $server the server array
     *
     * @return string
     */
    private static function fetchServerRequestUri(array $server)
    {
        if (isset($server['REQUEST_URI'])) {
            return $server['REQUEST_URI'];
        } elseif (isset($server['PHP_SELF'])) {
            return $server['PHP_SELF'];
        }

        return '/';
    }
}

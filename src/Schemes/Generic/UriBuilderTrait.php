<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Schemes\Generic;

use InvalidArgumentException;

/**
 * A trait to format the Path component
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.1.0
 * @internal
 */
trait UriBuilderTrait
{
    /**
     * Normalize URI components hash
     *
     * @internal
     *
     * @todo change method visibility in the next major release to protected
     *
     * @param array $components a hash representation of the URI components
     *                          similar to PHP parse_url function result
     *
     * @return array
     */
    public static function normalizeUriHash(array $components)
    {
        return array_replace([
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ], $components);
    }

    /**
     * Format the Path in a URI string
     *
     * @param string $path
     * @param string $authority the Authority part
     *
     * @return string
     */
    protected function formatPath($path, $authority)
    {
        if ('' == $authority) {
            return preg_replace(',^/+,', '/', $path);
        }

        if ('' !== $path && '/' != $path[0]) {
            return '/'.$path;
        }

        return $path;
    }

    /**
     * Format the user info
     *
     * @internal
     *
     * @todo change method visibility in the next major release to protected
     *
     * @param string $user
     * @param string $pass
     *
     * @return string
     */
    public static function buildUserInfo($user, $pass)
    {
        $userinfo = self::filterUser($user);
        if (null === $userinfo) {
            return '';
        }

        $pass = self::filterPass($pass);
        if (null !== $pass) {
            $userinfo .= ':'.$pass;
        }

        return $userinfo.'@';
    }

    /**
     * Filter and format the user for URI string representation
     *
     * @param null|string $user
     *
     * @throws InvalidArgumentException If the user is invalid
     *
     * @return null|string
     */
    protected static function filterUser($user)
    {
        if (!preg_match(',[/?#@:],', $user)) {
            return $user;
        }

        throw new InvalidArgumentException('The user component contains invalid characters');
    }

    /**
     * Filter and format the pass for URI string representation
     *
     * @param null|string $pass
     *
     * @throws InvalidArgumentException If the pass is invalid
     *
     * @return null|string
     */
    protected static function filterPass($pass)
    {
        if (!preg_match(',[/?#@],', $pass)) {
            return $pass;
        }

        throw new InvalidArgumentException('The pass component contains invalid characters');
    }
}

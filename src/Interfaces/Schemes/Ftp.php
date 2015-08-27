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
namespace League\Uri\Interfaces\Schemes;

use InvalidArgumentException;

/**
 * Value object representing a FTP URI.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986
 */
interface Ftp extends HierarchicalUri
{
    const TYPE_ASCII = 1;

    const TYPE_BINARY = 2;

    const TYPE_DIRECTORY = 3;

    const TYPE_NONE = 4;

    /**
     * Retrieve the optional type associated to the path component of the URI.
     *
     * The value returned MUST be one of the interface constant type
     * If no type is associated the return constant must be self::TYPE_NONE
     *
     * @see http://tools.ietf.org/html/rfc1738#section-3.2.2
     *
     * @return int a typecode constant.
     */
    public function getTypecode();

    /**
     * Return an instance with the specified typecode.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified type appended to the path.
     * if not
     *
     * Using self::TYPE_NONE is equivalent to removing the typecode.
     *
     * @param int $type one typecode constant.
     *
     * @throws InvalidArgumentException for invalid typecode.
     *
     * @return static
     *
     */
    public function withTypecode($type);
}

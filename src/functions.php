<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2016 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.2.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri;

use InvalidArgumentException;
use League\Uri\Interfaces\Uri;
use Psr\Http\Message\UriInterface;

/**
 * A function to give information about URI Reference
 *
 * This function returns an associative array representing the URI Reference information:
 * each key represents a given state and each value is a boolean to indicate the current URI
 * status against the declared state. For any given URI only one of the listed state can be valid.
 *
 * <ul>
 * <li>absolute_uri: Tell whether the URI is absolute (ie contains a non_empty scheme)
 * <li>network_path: Tell whether the URI is a network_path relative reference
 * <li>absolute_path: Tell whether the URI is a absolute_path relative reference
 * <li>relative_path: Tell whether the URI is a relative_path relative reference
 * </ul>
 *
 * @link  https://tools.ietf.org/html/rfc3986#section-4.2
 * @link  https://tools.ietf.org/html/rfc3986#section-4.3
 * @since 4.2.0
 *
 * @param Uri|UriInterface $uri
 *
 * @throws InvalidArgumentException if the submitted Uri is invalid
 *
 * @return array
 */
function uri_getinfo($uri)
{
    if (!$uri instanceof Uri && !$uri instanceof UriInterface) {
        throw new InvalidArgumentException(
            'URI passed must implement PSR_7 UriInterface or League\Uri Uri interface'
        );
    }

    $infos = [
        'absolute_uri' => false,
        'network_path' => false,
        'absolute_path' => false,
        'relative_path' => false,
    ];

    if ('' !== $uri->getScheme()) {
        $infos['absolute_uri'] = true;

        return $infos;
    }

    if ('' !== $uri->getAuthority()) {
        $infos['network_path'] = true;

        return $infos;
    }

    $path = $uri->getPath();
    if (isset($path[0]) && '/' === $path[0]) {
        $infos['absolute_path'] = true;

        return $infos;
    }

    $infos['relative_path'] = true;

    return $infos;
}

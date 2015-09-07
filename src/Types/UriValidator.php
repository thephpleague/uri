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
namespace League\Uri\Types;

use InvalidArgumentException;
use League\Uri\Interfaces\Uri as LeagueUriInterface;
use Psr\Http\Message\UriInterface;

/**
 * Uri Parameter validation
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait UriValidator
{
    /**
     * Assert the submitted object is a UriInterface object
     *
     * @param LeagueUriInterface|UriInterface $uri
     *
     * @throws InvalidArgumentException if the object does not implemet PSR-7 UriInterface
     */
    protected function assertUriObject($uri)
    {
        if (!$uri instanceof LeagueUriInterface && !$uri instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'URI passed must implement PSR-7 or League\Uri Uri interface; received "%s"',
                (is_object($uri) ? get_class($uri) : gettype($uri))
            ));
        }
    }
}

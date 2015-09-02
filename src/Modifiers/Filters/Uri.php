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
namespace League\Uri\Modifiers\Filters;

use League\Uri\Interfaces\Uri as UriInterface;
use Psr\Http\Message\UriInterface as Psr7UriInterface;

/**
 * Uri Parameter validation
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait Uri
{
    use UriValidator;

    /**
     * The list of keys to remove
     *
     * @var UriInterface|Psr7UriInterface
     */
    protected $uri;

    /**
     * Return a new instance with a new set of keys
     *
     * @param UriInterface|Psr7UriInterface $uri The Uri Object
     *
     * @return $this
     */
    public function withUri($uri)
    {
        $clone = clone $this;
        $clone->uri = $this->filterUri($uri);

        return $clone;
    }

    /**
     * Validate the submitted keys
     *
     * @param UriInterface|Psr7UriInterface $uri The Uri Object
     *
     * @return callable|array
     */
    protected function filterUri($uri)
    {
        $this->assertUriObject($uri);

        return $uri;
    }
}

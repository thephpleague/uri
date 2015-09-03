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
namespace League\Uri;

use InvalidArgumentException;
use League\Uri\Interfaces\Uri;
use League\Uri\Modifiers\Filters\UriValidator;
use Psr\Http\Message\UriInterface;
use RuntimeException;

/**
 * A class to ease applying multiple modification 
 * on a URI object based on the pipeline pattern
 * This class is based on league.pipeline
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
class Pipeline
{
    use UriValidator;

    /**
     * @var callable[]
     */
    protected $collection = [];

    /**
     * New instance
     *
     * @param callable[] $collection
     *
     * @throws InvalidArgumentException
     */
    public function __construct($collection = [])
    {
        foreach ($collection as $stage) {
            if (!is_callable($stage)) {
                throw new InvalidArgumentException('All collection should be callable');
            }
        }
        $this->collection = $collection;
    }

    /**
     * Create a new modifier with an appended stage.
     *
     * @param callable $stage
     *
     * @return static
     */
    public function pipe(callable $stage)
    {
        $collection = $this->collection;
        $collection[] = $stage;

        return new static($collection);
    }

    /**
     * Return a Uri object modified according to the modifier
     *
     * @param Uri|UriInterface $uri
     *
     * @throws RuntimeException if the returned value is not of the 
     *                          same class as the submitted URI object
     *
     * @return Uri|UriInterface
     */
    public function __invoke($uri)
    {
        $this->assertUriObject($uri);

        $reducer = function ($uri, callable $stage) {
            return call_user_func($stage, $uri);
        };

        if (!is_object($newUri) || get_class($newUri) !== get_class($uri)) {
            throw new RuntimeException(
                'The returned value is not of the same class as the submitted URI object'
            );
        }

        return array_reduce($this->collection, $reducer, $uri);
    }
}

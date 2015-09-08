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
use League\Uri\Modifiers\ModifierCollection;
use League\Uri\Types\UriValidator;
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
     * @var ModifierCollection
     */
    protected $collection;

    /**
     * New instance
     *
     * @param callable[] $modifiers
     *
     * @throws InvalidArgumentException
     */
    public function __construct($collection = [])
    {
        foreach ($collection as $stage) {
            if (!is_callable($stage)) {
                throw new InvalidArgumentException('All collection should be callable');
            }

            $this->collection[] = $stage;
        }
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
     * @return Uri|UriInterface
     */
    public function process($uri)
    {
        return $this->__invoke($uri);
    }

    /**
     * Iteratively apply the modifier to a URI object
     *
     * @param Uri|UriInterface $uri
     *
     * @throws RuntimeException If the resulting URI is not an URI Object
     *
     * @return Uri|UriInterface
     */
    public function __invoke($uri)
    {
        $this->assertUriObject($uri);
        $submittedUriClass = get_class($uri);
        foreach ($this->collection as $modifier) {
            $uri = call_user_func($modifier, $uri);
            if (!is_object($uri) || $submittedUriClass !== get_class($uri)) {
                throw new RuntimeException(
                    'The returned value is not of the same class as the submitted URI object'
                );
            }
        }

        return $uri;
    }
}

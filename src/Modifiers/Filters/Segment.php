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

use League\Uri\Components\HierarchicalPath;
use League\Uri\Interfaces\Path;
use League\Uri\Types\ValidatorTrait;

/**
 * Path Segment validation trait
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait Segment
{
    use ValidatorTrait;

    /**
     * A HierarchicalPath object
     *
     * @var HierarchicalPath
     */
    protected $segment;

    /**
     * Return a instance with the specified path
     *
     * @param string $segment the data to be merged query can be
     *
     * @return self
     */
    public function withSegment($segment)
    {
        $segment = $this->filterSegment($segment);
        $clone = clone $this;
        $clone->segment = $segment;

        return $clone;
    }

    /**
     * Filter and validate the path data
     *
     * @param string $path the data to be merged query can be
     *
     * @return HierarchicalPath
     */
    protected function filterSegment($path)
    {
        return new HierarchicalPath($this->validateString($path));
    }
}

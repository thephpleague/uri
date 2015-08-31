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
use League\Uri\Interfaces\Components\HierarchicalPath as HierarchicalPathInterface;

/**
 * Path Segment validation trait
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait Segment
{
    /**
     * A HierarchicalPath object
     *
     * @var HierarchicalPathInterface
     */
    protected $segment;

    /**
     * Return a instance with the specified path
     *
     * @param HierarchicalPathInterface|string $segment the data to be merged query can be
     *
     * @return self
     */
    public function withSegment($segment)
    {
        $clone = clone $this;
        $clone->segment = $this->filterSegment($segment);

        return $clone;
    }

    /**
     * Filter and validate the path data
     *
     * @param HierarchicalPathInterface|string $path the data to be merged query can be
     *
     * @return HierarchicalPathInterface
     */
    protected function filterSegment($path)
    {
        if ($path instanceof HierarchicalPathInterface) {
            return $path;
        }

        return new HierarchicalPath($path);
    }
}

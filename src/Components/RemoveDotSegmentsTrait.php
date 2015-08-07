<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Components;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @since  4.0.0
 */
trait RemoveDotSegmentsTrait
{
    /**
     * Dot Segment pattern
     *
     * @var array
     */
    protected static $dot_segments = ['.' => 1, '..' => 1];

    /**
     * {@inheritdoc}
     */
    abstract public function __toString();

    /**
     * {@inheritdoc}
     */
    abstract public function modify($value);

    /**
     * {@inheritdoc}
     */
    public function withoutDotSegments()
    {
        $current = $this->__toString();
        if (false === strpos($current, '.')) {
            return $this;
        }

        $input = explode('/', $current);
        $new   = implode('/', array_reduce($input, [$this, 'filterDotSegments'], []));
        if (isset(static::$dot_segments[end($input)])) {
            $new .= static::$separator;
        }

        return $this->modify($new);
    }

    /**
     * Filter Dot segment according to RFC3986
     *
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
     *
     * @param array  $carry   Path segments
     * @param string $segment a path segment
     *
     * @return array
     */
    protected function filterDotSegments(array $carry, $segment)
    {
        if ('..' == $segment) {
            array_pop($carry);

            return $carry;
        }

        if (!isset(static::$dot_segments[$segment])) {
            $carry[] = $segment;
        }

        return $carry;
    }
}

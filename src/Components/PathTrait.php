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
namespace League\Uri\Components;

use InvalidArgumentException;
use League\Uri\Interfaces\Path as PathInterface;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
trait PathTrait
{
    /**
     * Typecode Regular expression
     */
    protected static $typeRegex = ',^(?P<basename>.*);type=(?P<typecode>a|i|d)$,';

    /**
     * Typecode value
     *
     * @array
     */
    protected static $typecodeList = [
        'a' => PathInterface::FTP_TYPE_ASCII,
        'i' => PathInterface::FTP_TYPE_BINARY,
        'd' => PathInterface::FTP_TYPE_DIRECTORY,
        ''  => PathInterface::FTP_TYPE_EMPTY,
    ];

    /**
     * Dot Segment pattern
     *
     * @var array
     */
    protected static $dotSegments = ['.' => 1, '..' => 1];

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
        if (isset(static::$dotSegments[end($input)])) {
            $new .= '/';
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

        if (!isset(static::$dotSegments[$segment])) {
            $carry[] = $segment;
        }

        return $carry;
    }

    /**
     * {@inheritdoc}
     */
    public function relativize(PathInterface $path)
    {
        $bSegments = explode('/', $this->withoutDotSegments()->__toString());
        $cSegments = explode('/', $path->withoutDotSegments()->__toString());
        if ('' == end($bSegments)) {
            array_pop($bSegments);
        }

        $key = 0;
        $res = ['..'];
        while (isset($cSegments[$key], $bSegments[$key]) && $cSegments[$key] === $bSegments[$key]) {
            ++$key;
            $res[] = '..';
        }

        $segments = array_slice($cSegments, $key);
        if (count($bSegments) > count($cSegments)) {
            $segments = array_merge($res, $segments);
        }

        return $this->modify(implode('/', $segments))->withoutEmptySegments();
    }

    /**
     * {@inheritdoc}
     */
    public function withoutEmptySegments()
    {
        return $this->modify(preg_replace(',/+,', '/', $this->__toString()));
    }

    /**
     * {@inheritdoc}
     */
    public function hasTrailingSlash()
    {
        $str = $this->__toString();

        return !empty($str) && '/' === mb_substr($str, -1, 1, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function withTrailingSlash()
    {
        return $this->hasTrailingSlash() ? $this : $this->modify($this->__toString().'/');
    }

    /**
     * {@inheritdoc}
     */
    public function withoutTrailingSlash()
    {
        return !$this->hasTrailingSlash() ? $this : $this->modify(mb_substr($this->__toString(), 0, -1, 'UTF-8'));
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        $path = $this->__toString();

        return !empty($path) && '/' === mb_substr($path, 0, 1, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function withLeadingSlash()
    {
        return $this->isAbsolute() ? $this : $this->modify('/'.$this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function withoutLeadingSlash()
    {
        return !$this->isAbsolute() ? $this : $this->modify(mb_substr($this->__toString(), 1, null, 'UTF-8'));
    }

    /**
     * {@inheritdoc}
     */
    public function getTypecode()
    {
        if (preg_match(self::$typeRegex, $this->__toString(), $matches)) {
            return self::$typecodeList[$matches['typecode']];
        }

        return PathInterface::FTP_TYPE_EMPTY;
    }

    /**
     * {@inheritdoc}
     */
    public function withTypecode($type)
    {
        if (!in_array($type, self::$typecodeList)) {
            throw new InvalidArgumentException('invalid typecode');
        }

        $path = $this->__toString();
        if (preg_match(self::$typeRegex, $path, $matches)) {
            $path = $matches['basename'];
        }

        $extension = array_search($type, self::$typecodeList);
        if (!empty($extension)) {
            $extension = ';type='.$extension;
        }

        return $this->modify($path.$extension);
    }

    /**
     * Encode a segment or the entire path string
     *
     * @param  array  $matches
     * @return string
     */
    protected function decodeSegmentPart(array $matches)
    {
        return rawurldecode(array_shift($matches));
    }
}

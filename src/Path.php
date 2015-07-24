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
namespace League\Uri;

use InvalidArgumentException;

/**
 * Value object representing a URI path component.
 *
 * @package League.uri
 * @since 1.0.0
 */
class Path extends AbstractHierarchicalComponent implements Interfaces\Path
{
    /**
     * {@inheritdoc}
     */
    protected static $characters_set = [
        '/', ':', '@', '!', '$', '&', "'",
        '(', ')', '*', '+', ',', ';', '=', '?',
    ];

    /**
     * {@inheritdoc}
     */
    protected static $characters_set_encoded = [
        '%2F', '%3A', '%40', '%21', '%24', '%26', '%27',
        '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D', '%3F',
    ];

    /**
     * Dot Segment pattern
     *
     * @var array
     */
    protected static $dot_segments = ['.' => 1, '..' => 1];

    /**
     * HierarchicalComponent delimiter
     *
     * @var string
     */
    protected static $delimiter = '/';

    /**
     * {@inheritdoc}
     */
    protected function init($str)
    {
        $str = $this->validateString($str);
        $this->isAbsolute = self::IS_RELATIVE;
        if (static::$delimiter == mb_substr($str, 0, 1, 'UTF-8')) {
            $this->isAbsolute = self::IS_ABSOLUTE;
            $str = mb_substr($str, 1, mb_strlen($str), 'UTF-8');
        }

        $append_delimiter = false;
        if (static::$delimiter === mb_substr($str, -1, 1, 'UTF-8')) {
            $str = mb_substr($str, 0, -1, 'UTF-8');
            $append_delimiter = true;
        }

        $this->data = $this->validate($str);
        if ($append_delimiter) {
            $this->data[] = '';
        }
    }

    /**
     * validate the submitted data
     *
     * @param string $data
     *
     * @throws InvalidArgumentException If reserved characters are used
     *
     * @return array
     */
    protected function validate($data)
    {
        if (preg_match('/[?#]/', $data)) {
            throw new InvalidArgumentException('the path must not contain a query string or a URI fragment');
        }

        $data = array_values(array_filter(explode(static::$delimiter, $data), function ($value) {
            return !is_null($value);
        }));

        return array_map(function ($value) {
            return rawurldecode(filter_var($value, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]));
        }, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSegment($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $front_delimiter = '';
        if ($this->isAbsolute == self::IS_ABSOLUTE) {
            $front_delimiter = static::$delimiter;
        }

        return $front_delimiter . implode(static::$delimiter, array_map([$this, 'encode'], $this->data));
    }

    /**
     * {@inheritdoc}
     */
    public function withoutDotSegments()
    {
        $current = $this->__toString();
        if (false === strpos($current, '.')) {
            return $this;
        }

        $input = explode(static::$delimiter, $current);
        $new   = implode(static::$delimiter, array_reduce($input, [$this, 'filterDotSegments'], []));
        if (isset(static::$dot_segments[end($input)])) {
            $new .= static::$delimiter;
        }

        return $this->modify($new);
    }

    /**
     * {@inheritdoc}
     */
    public function relativize(Interfaces\Path $path)
    {
        $bSegments = explode(static::$delimiter, $this->withoutDotSegments()->__toString());
        $cSegments = explode(static::$delimiter, $path->withoutDotSegments()->__toString());
        if ('' == end($bSegments)) {
            array_pop($bSegments);
        }

        $key = 0;
        $res = [];
        while (isset($cSegments[$key], $bSegments[$key]) && $cSegments[$key] === $bSegments[$key]) {
            ++$key;
            $res[] = '..';
        }

        return static::createFromArray(array_merge($res, array_slice($cSegments, $key)));
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
    public function getBasename()
    {
        $data = $this->data;

        return (string) array_pop($data);
    }

    /**
     * {@inheritdoc}
     */
    public function hasTrailingSlash()
    {
        return !$this->isEmpty() && empty($this->getBasename());
    }

    /**
     * {@inheritdoc}
     */
    public function withTrailingSlash()
    {
        return $this->hasTrailingSlash() ? $this : $this->modify($this->__toString() . static::$delimiter);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutTrailingSlash()
    {
        return !$this->hasTrailingSlash() ? $this : $this->modify(substr($this->__toString(), 0, -1));
    }

    /**
     * {@inheritdoc}
     */
    public function getDirname()
    {
        return str_replace("\0", '\\', pathinfo(str_replace('\\', "\0", $this), PATHINFO_DIRNAME));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return pathinfo($this->getBasename(), PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension($ext)
    {
        $ext = ltrim($ext, '.');
        if (strpos($ext, static::$delimiter)) {
            throw new InvalidArgumentException('an extension sequence can not contain a path delimiter');
        }
        $ext         = implode(static::$delimiter, $this->validate($ext));
        $data        = $this->data;
        $basename    = (string) array_pop($data);
        $newBasename = $this->setBasename($basename, $ext);
        if ($newBasename == $basename) {
            return $this;
        }
        $data[] = $newBasename;

        return $this->newCollectionInstance($data);
    }

    /**
     * Set a new extension to a basename
     *
     * @param string $basename the current basename
     * @param string $ext      the new extension to use
     *
     * @return string
     */
    protected function setBasename($basename, $ext)
    {
        $length = mb_strlen(pathinfo($basename, PATHINFO_EXTENSION), 'UTF-8');
        if ($length > 0) {
            $basename = mb_substr($basename, 0, -($length + 1), 'UTF-8');
        }

        if (empty($basename) || empty($ext)) {
            return $basename;
        }

        return "$basename.$ext";
    }
}

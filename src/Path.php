<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url;

use InvalidArgumentException;

/**
 * Value object representing a URL path component.
 *
 * @package League.url
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
     * New Instance of Path
     *
     * @param string $str the path
     */
    protected function init($str)
    {
        $this->is_absolute = self::IS_RELATIVE;
        $str = $this->validateString($str);
        if (preg_match(',^/+$,', $str)) {
            $this->is_absolute = self::IS_ABSOLUTE;
            return;
        }

        if (static::$delimiter == mb_substr($str, 0, 1, 'UTF-8')) {
            $this->is_absolute =  self::IS_ABSOLUTE;
        }
        $append_delimiter = static::$delimiter === mb_substr($str, -1, 1, 'UTF-8');
        $str = trim($str, static::$delimiter);
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
            throw new InvalidArgumentException('the path must not contain a query string or a URL fragment');
        }

        $data = array_values(array_filter(explode(static::$delimiter, $data), function ($value) {
            return !is_null($value);
        }));

        return array_map(function ($value) {
            return rawurldecode(filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]));
        }, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSegment($offset, $default = null)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $front_delimiter = '';
        if ($this->is_absolute == self::IS_ABSOLUTE) {
            $front_delimiter = static::$delimiter;
        }

        return $front_delimiter.implode(static::$delimiter, array_map([$this, 'encode'], $this->data));
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
        $new   = '';
        if (static::$delimiter == $current[0]) {
            $new = static::$delimiter;
        }
        $new .= implode(static::$delimiter, $this->filterDotSegments($input));
        if (isset(static::$dot_segments[end($input)])) {
            $new .= static::$delimiter;
        }

        return new static($new);
    }

    /**
     * Filter Dot segment according to RFC3986
     *
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
     *
     * @param array $input Path segments
     *
     * @return array
     */
    protected function filterDotSegments(array $input)
    {
        $arr = [];
        foreach ($input as $segment) {
            if ('..' == $segment) {
                array_pop($arr);
                continue;
            }

            if (!isset(static::$dot_segments[$segment])) {
                $arr[] = $segment;
            }
        }

        return $arr;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutEmptySegments()
    {
        $current = $this->__toString();
        $new = preg_replace(',/+,', '/', $current);

        if ($current == $new) {
            return $this;
        }

        return new static($new);
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
    public function getDirname()
    {
        return str_replace("\0", "\\", pathinfo(str_replace("\\", "\0", $this), PATHINFO_DIRNAME));
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
        $ext = implode(static::$delimiter, $this->validate($ext));
        $data = $this->data;
        $basename = (string) array_pop($data);
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
            $basename = mb_substr($basename, 0, -($length+1), 'UTF-8');
        }

        if (empty($basename) || empty($ext)) {
            return $basename;
        }

        return "$basename.$ext";
    }
}

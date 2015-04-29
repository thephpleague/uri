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
use League\Url\Interfaces;
use LogicException;
use Traversable;

/**
* A class to manipulate URL Path component
*
* @package League.url
* @since 1.0.0
*/
class Path extends AbstractSegment implements Interfaces\Path
{
    /**
     * Pattern to conform to Path RFC - http://tools.ietf.org/html/rfc3986#appendix-A
     *
     * @var array
     */
    protected static $sanitizePattern = [
        '%2F', '%3A', '%40', '%21', '%24', '%26', '%27',
        '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D', '%3F',
    ];

    /**
     * Pattern to conform to Path RFC - http://tools.ietf.org/html/rfc3986#appendix-A
     *
     * @var array
     */
    protected static $sanitizeReplace = [
        '/', ':', '@', '!', '$', '&', "'",
        '(', ')', '*', '+', ',', ';', '=', '?',
    ];

    protected static $dot_segments = ['.' => 1, '..' => 1];

    /**
     * Segment delimiter
     *
     * @var string
     */
    protected static $delimiter = '/';

    /**
     * Is the path absolute
     *
     * @var bool
     */
    protected $is_absolute = false;

    /**
     * New Instance of Path
     *
     * @param string $str the path
     */
    public function __construct($str = null)
    {
        $str = $this->validateString($str);
        if (preg_match(',^/+$,', $str)) {
            $this->is_absolute = true;
            return;
        }

        $this->is_absolute = static::$delimiter == mb_substr($str, 0, 1);
        $append_delimiter  = static::$delimiter === mb_substr($str, -1, 1);
        $str = trim($str, static::$delimiter);
        $this->data = $this->validate($str);
        if ($append_delimiter) {
            $this->data[] = '';
        }
    }

    /**
     * return a new Host instance from an Array or a traversable object
     *
     * @param \Traversable|array $data
     * @param bool               $is_absolute
     *
     * @throws \InvalidArgumentException If $data is invalid
     *
     * @return static
     */
    public static function createFromArray($data, $is_absolute = false)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data, false);
        }

        if (! is_array($data)) {
            throw new InvalidArgumentException('Data passed to the method must be an array or a Traversable object');
        }

        $path = '';
        if ($is_absolute) {
            $path = static::$delimiter;
        }
        $path .= implode(static::$delimiter, $data);

        return new static($path);
    }
    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = array_values(array_filter(explode(static::$delimiter, $data), function ($value) {
            return ! is_null($value);
        }));

        return array_map(function ($value) {
            $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);

            return str_replace(self::$sanitizePattern, self::$sanitizeReplace, rawurlencode(rawurldecode($value)));
        }, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute()
    {
        return $this->is_absolute;
    }

    /**
     * {@inheritdoc}
     */
    public function getSegment($key, $default = null)
    {
        if ($this->hasKey($key)) {
            return rawurldecode($this->data[$key]);
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $front_delimiter = '';
        if ($this->is_absolute) {
            $front_delimiter = static::$delimiter;
        }

        return $front_delimiter.implode(static::$delimiter, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return $this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function normalize()
    {
        $current = $this->__toString();
        if (false === strpos($current, '.')) {
            return clone $this;
        }

        $input    = explode(static::$delimiter, $current);
        $new_path = '';
        if (static::$delimiter == $current[0]) {
            $new_path = static::$delimiter;
        }
        $new_path .= implode(static::$delimiter, $this->filterDotSegment($input));
        if (isset(static::$dot_segments[end($input)])) {
            $new_path .= static::$delimiter;
        }

        return new static($new_path);
    }

    /**
     * Filter Dot Segments
     *
     * @param  array  $input
     * @param  array  $dot_segments
     *
     * @return array
     */
    protected function filterDotSegment(array $input)
    {
        $arr = [];
        foreach ($input as $segment) {
            if ('..' == $segment) {
                array_pop($arr);
                continue;
            }

            if (! isset(static::$dot_segments[$segment])) {
                $arr[] = $segment;
            }
        }

        return $arr;
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
        $ext      = implode(static::$delimiter, $this->validate($ext));
        $data     = $this->data;
        $basename = (string) array_pop($data);
        if ('' == $basename) {
            throw new LogicException('No basename exist!!');
        }
        $current_ext = pathinfo($basename, PATHINFO_EXTENSION);
        if ('' != $current_ext) {
            $basename = mb_substr($basename, 0, -mb_strlen($current_ext)-1);
        }
        $data[] = "$basename.$ext";

        return static::createFromArray($data, $this->is_absolute);
    }
}

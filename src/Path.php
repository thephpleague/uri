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

/**
* Value object representing a URL path component.
*
* @package League.url
* @since 1.0.0
*/
class Path extends AbstractCollectionComponent implements Interfaces\Path
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

    protected static $dot_CollectionComponents = ['.' => 1, '..' => 1];

    /**
     * CollectionComponent delimiter
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
     * validate the submitted data
     *
     * @param string $data
     *
     * @return array
     */
    protected function validate($data)
    {
        $data = array_values(array_filter(explode(static::$delimiter, $data), function ($value) {
            return ! is_null($value);
        }));

        return array_map(function ($value) {
            $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);

            return str_replace(static::$sanitizePattern, static::$sanitizeReplace, rawurlencode(rawurldecode($value)));
        }, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSegment($offset, $default = null)
    {
        if (isset($this->data[$offset])) {
            return rawurldecode($this->data[$offset]);
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
    public function normalize()
    {
        $current = $this->__toString();
        if (false === strpos($current, '.')) {
            return $this;
        }

        $input = explode(static::$delimiter, $current);
        $new_path = '';
        if (static::$delimiter == $current[0]) {
            $new_path = static::$delimiter;
        }
        $new_path .= implode(static::$delimiter, $this->filterDotSegment($input));
        if (isset(static::$dot_CollectionComponents[end($input)])) {
            $new_path .= static::$delimiter;
        }

        return new static($new_path);
    }

    /**
     * Filter Dot CollectionComponents
     *
     * @param array $input
     *
     * @return array
     */
    protected function filterDotSegment(array $input)
    {
        $arr = [];
        foreach ($input as $CollectionComponent) {
            if ('..' == $CollectionComponent) {
                array_pop($arr);
                continue;
            }

            if (! isset(static::$dot_CollectionComponents[$CollectionComponent])) {
                $arr[] = $CollectionComponent;
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

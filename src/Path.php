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
use League\Url\Interfaces\Path as PathInterface;
use League\Url\Util;
use LogicException;

/**
* A class to manipulate URL Path component
*
* @package League.url
* @since 1.0.0
*/
class Path extends AbstractSegment implements PathInterface
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

    /**
     * Segment delimiter
     *
     * @var string
     */
    protected $delimiter = '/';

    /**
     * Does the path contain a leading delimiter
     * @var bool
     */
    protected $has_front_delimiter = false;

    /**
     * Trait to validate a stringable variable
     */
    use Util\StringValidator;

    /**
     * New Instance of Path
     *
     * @param string $str the path
     */
    public function __construct($str = null)
    {
        $str = $this->validateString($str);

        if (strpos($str, '#') !== false || strpos($str, '?') !== false) {
            throw new InvalidArgumentException('Data passed must be a valid string;');
        }

        if (preg_match(',^/+$,', $str)) {
            $this->has_front_delimiter = true;
            return;
        }

        $this->has_front_delimiter = $this->delimiter == mb_substr($str, 0, 1);
        $append_delimiter          = $this->delimiter === mb_substr($str, -1, 1);
        $str = trim($str, $this->delimiter);
        $this->data = $this->validate($str);
        if ($append_delimiter) {
            $this->data[] = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = array_values(array_filter(explode($this->delimiter, $data), function ($value) {
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
    public function getData($key, $default = null)
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
        if ($this->has_front_delimiter) {
            $front_delimiter = $this->delimiter;
        }

        return $front_delimiter.implode($this->delimiter, $this->data);
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
        $input = $this->__toString();
        if (empty($input) || false === strpos($input, '.')) {
            return clone $this;
        }
        $prepend_slash = ($this->delimiter == $input[0]) ? $this->delimiter : '';
        $append_slash  = ($this->delimiter == mb_substr($input, -1, 1)) ? $this->delimiter : '';

        $input  = explode($this->delimiter, trim($input));
        $output = [];
        foreach ($input as $segment) {
            if ('.' == $segment) {
                continue;
            }

            if ('..' == $segment) {
                array_pop($output);
                continue;
            }

            $output[] = $segment;
        }

        if (count($output) && in_array(end($input), ['.', '..'])) {
            $append_slash = $this->delimiter;
        }

        return new static($prepend_slash.implode($this->delimiter, $output).$append_slash);
    }

    /**
     * {@inheritdoc}
     */
    public function getBasename()
    {
        $data = $this->data;

        return array_pop($data);
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
        $ext = trim($ext);
        $ext = ltrim($ext, '.');
        if (strpos($ext, $this->delimiter)) {
            throw new InvalidArgumentException('an extension sequence can not contain a path delimiter');
        }
        $ext = implode($this->delimiter, $this->validate($ext));

        $basename = $this->getBasename();
        if ('' == $basename) {
            throw new LogicException('No basename exist!!');
        }
        $current_ext = pathinfo($basename, PATHINFO_EXTENSION);
        if ('' != $current_ext) {
            $basename = mb_substr(0, mb_strlen($current_ext) - 1);
        }

        return $this->replaceWith("/$basename.$ext", count($this->data) - 1);
    }
}

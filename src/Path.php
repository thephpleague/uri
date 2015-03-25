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

/**
* A class to manipulate URL Path component
*
* @package League.url
* @since 1.0.0
*/
class Path extends AbstractSegment implements PathInterface
{
    /**
     * {@inheritdoc}
     */
    protected $delimiter = '/';

    protected $sanitizePattern = [
        '%2F', '%3A', '%40', '%21', '%24', '%26', '%27',
        '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D'
    ];

    protected $sanitizeReplace = [
        '/', ':', '@', '!', '$', '&', "'",
        '(', ')', '*', '+', ',', ';', '='
    ];

    public function __construct($str = null)
    {
        if (is_null($str)) {
            $this->data = [];
            return;
        }

        if (! is_scalar($str) || (is_object($str) && ! method_exists($str, '__toString'))) {
            throw new InvalidArgumentException('Invalid data to create a new Path instance');
        }

        $str = trim($str);
        $str = ltrim($str, '/');
        $this->data = $this->validate($str);
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = array_values(array_filter(explode('/', $data), function ($value) {
            return ! is_null($value);
        }));

        return array_map(function ($value) {
            $value = filter_var($value, FILTER_UNSAFE_RAW, ["flags" => FILTER_FLAG_STRIP_LOW]);

            return str_replace($this->sanitizePattern, $this->sanitizeReplace, rawurlencode(rawurldecode($value)));
        }, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (! $this->data) {
            return null;
        }

        return implode('/', $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($key, $default = null)
    {
        if ($this->hasKey($key)) {
            return rawurldecode($this->data[$key]);
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) '/'.$this->get();
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
        if (false === strpos($input, '.')) {
            return new static($input);
        }
        $output = [];
        while ('' != $input) {
            if ('/.' == $input) {
                $output[] = '/';
                break;
            } elseif ('/./' == substr($input, 0, 3)) {
                $input = substr($input, 2);
                continue;
            } elseif ('/..' == $input) {
                array_pop($output);
                $output[] = '/';
                break;
            } elseif ('/../' == substr($input, 0, 4)) {
                array_pop($output);
                $input = substr($input, 3);
            } elseif (in_array($input, ['.', '..'])) {
                break;
            } elseif (false === ($pos = stripos($input, '/', 1))) {
                $output[] = $input;
                break;
            } else {
                $output[] = substr($input, 0, $pos);
                $input = substr($input, $pos);
            }
        }

        return new static(implode($output));
    }
}

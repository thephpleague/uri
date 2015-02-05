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

use Countable;
use IteratorAggregate;
use League\Url\Interfaces\PathInterface;
use OutOfBoundsException;

/**
 *  A class to manipulate URL Path component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Path extends AbstractSegment implements
    Countable,
    IteratorAggregate,
    PathInterface
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


    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $res = [];
        foreach (array_values($this->data) as $value) {
            $res[] = rawurlencode($value);
        }
        if (! $res) {
            return null;
        }

        return implode($this->delimiter, $res);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        return '/'.$this->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->get();
    }

    /**
     * Remove dot segments from a URI path according to RFC3986 Section 5.2.4
     *
     * @return  static
     *
     * @link http://www.ietf.org/rfc/rfc3986.txt
     */
    public function normalize()
    {
        $path = $this->getUriComponent();
        if (false === strpos($path, '.')) {
            return new static($path);
        }

        $input  = $path;
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

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = $this->sanitizeValue($this->validateSegment($data));

        return array_map('urldecode', $data);
    }

    /**
     * Sanitize a string component recursively
     *
     * @param mixed $str
     *
     * @return mixed
     */
    protected function sanitizeValue($str)
    {
        $str = parent::sanitizeValue($str);
        if (is_array($str)) {
            return array_map([$this, 'sanitizeSegment'], $str);
        }

        return $this->sanitizeSegment($str);
    }

    protected function sanitizeSegment($str)
    {
        return str_replace(
            $this->sanitizePattern,
            $this->sanitizeReplace,
            rawurlencode(rawurldecode($str))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function formatRemoveSegment($data)
    {
        return array_map('urldecode', parent::formatRemoveSegment($data));
    }

    /**
     * {@inheritdoc}
     */
    public function getSegment($offset, $default = null)
    {
        $offset = filter_var($offset, FILTER_VALIDATE_INT, ['options' => ["min_range" => 0]]);
        if (false === $offset || ! isset($this->data[$offset])) {
            return $default;
        }

        return $this->data[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function setSegment($offset, $value)
    {
        $offset = filter_var($offset, FILTER_VALIDATE_INT, ['options' => [
            "min_range" => 0,
            "max_range" => $this->count(),
        ]]);
        if (false === $offset) {
            throw new OutOfBoundsException('The specified key is not in the object boundaries');
        }

        $data = $this->data;
        $value = filter_var((string) $value, FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_STRIP_LOW]);
        $value = trim($value);

        if (empty($value)) {
            unset($data[$offset]);
            return $this->set(array_values($data));
        }

        $data[$offset] = $value;

        return $this->set(array_values($data));
    }
}

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
     * {@inheritdoc}
     */
    public function normalize()
    {
        $pattern_a = '!^(\.\./|\./)!x';
        $pattern_b_1 = '!^(/\./)!x';
        $pattern_b_2 = '!^(/\.)$!x';
        $pattern_c = '!^(/\.\./|/\.\.)!x';
        $pattern_d = '!^(\.|\.\.)$!x';
        $pattern_e = '!(/*[^/]*)!x';
        $path = $this->getUriComponent();
        $new_path = '';
        while (! empty($path)) {
            if (preg_match($pattern_a, $path)) {
                // remove prefix from $path
                $path = preg_replace($pattern_a, '', $path);
            } elseif (preg_match($pattern_b_1, $path, $matches) || preg_match($pattern_b_2, $path, $matches)) {
                $path = preg_replace("!^".$matches[1]."!", '/', $path);
            } elseif (preg_match($pattern_c, $path, $matches)) {
                $path = preg_replace('!^'.preg_quote($matches[1], '!').'!x', '/', $path);
                // remove the last segment and its preceding "/" (if any) from output buffer
                $new_path = preg_replace('!/([^/]+)$!x', '', $new_path);
            } elseif (preg_match($pattern_d, $path)) {
                $path = preg_replace($pattern_d, '', $path);
            } elseif (preg_match($pattern_e, $path, $matches)) {
                $first_path_segment = $matches[1];
                $path = preg_replace('/^'.preg_quote($first_path_segment, '/').'/', '', $path, 1);
                $new_path .= $first_path_segment;
            }
        }

        return new static($new_path);
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

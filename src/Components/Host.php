<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.0.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Components;

use InvalidArgumentException;
use League\Url\Interfaces\SegmentInterface;

/**
 *  A class to manipulate URL Host component
 *
 *  @package League.url
 */
class Host extends AbstractSegment implements SegmentInterface
{
    /**
     * {@inheritdoc}
     */
    protected $delimiter = '.';

    /**
     * {@inheritdoc}
     */
    protected $regexStart = '@(:?^|\.)';

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (! $this->data) {
            return null;
        }

        return implode($this->delimiter, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->prependSegment(
            $this->data,
            $this->validate($data, $this->data),
            $whence,
            $whence_index
        );
    }

    /**
     * {@inheritdoc}
     */
    public function append($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->appendSegment(
            $this->data,
            $this->validate($data, $this->data),
            $whence,
            $whence_index
        );
    }

    /**
     * Validate Host data before insertion into a URL host component
     *
     * @param mixed $data the data to insert
     * @param array $host an array representation of a host component
     *
     * @return array
     *
     * @throws InvalidArgumentException If the added is invalid
     */
    protected function validate($data, array $host = array())
    {
        $data = $this->validateSegment($data, $this->delimiter);
        $res = preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT);
        if (count($res)) {
            throw new InvalidArgumentException(
                'Invalid host label, check its length and/or its characters'
            );
        }

        $imploded = implode($this->delimiter, $data);
        if (127 <= (count($host) + count($data))) {
            throw new InvalidArgumentException('Host may have at maximum 127 parts');
        } elseif (225 <= (strlen(implode($this->delimiter, $host)) + strlen($imploded) + 1)) {
            throw new InvalidArgumentException('Host may have a maximum of 255 characters');
        }

        return $this->sanitizeValue($data);
    }
}

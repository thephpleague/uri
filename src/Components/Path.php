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

use League\Url\Interfaces\SegmentInterface;

class Path extends AbstractSegment implements SegmentInterface
{

    protected $delimiter = '/';

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        return $this->validateSegment($data, $this->delimiter);
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (! $this->data) {
            return null;
        }

        return implode($this->delimiter, str_replace(' ', '%20', $this->data));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return str_replace(null, '', $this->get());
    }

    /**
     * {@inheritdoc}
     */
    public function append($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->appendSegment(
            $this->data,
            $this->validateSegment($data, $this->delimiter),
            $whence,
            $whence_index
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($data, $whence = null, $whence_index = null)
    {
        $this->data = $this->prependSegment(
            $this->data,
            $this->validateSegment($data, $this->delimiter),
            $whence,
            $whence_index
        );
    }
}

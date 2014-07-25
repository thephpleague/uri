<?php
/**
* This file is part of the League.url library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/thephpleague/url/
* @version 3.2.0
* @package League.url
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace League\Url\Components;

/**
 *  A class to manipulate URL Path component
 *
 *  @package League.url
 *  @since  1.0.0
 */
class Path extends AbstractSegment implements PathInterface
{
    /**
     * {@inheritdoc}
     */
    protected $delimiter = '/';

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $res = array();
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
    public function getRelativePath(PathInterface $path)
    {
        if ($this->sameValueAs($path)) {
            return '';
        }

        $ref_path = array_values($path->toArray());
        $this_path = array_values($this->data);
        $filename = array_pop($this_path);
        $common = 0;
        foreach ($ref_path as $offset => $value) {
            if (! isset($this_path[$offset]) || $value != $this_path[$offset]) {
                break;
            }
            $common++;
        }
        $start_index = count($ref_path) - $common;

        $clone = clone $this;
        $clone->set(array_merge(
            array_fill(0, $start_index, '..'),
            array_slice($this_path, $start_index),
            array($filename)
        ));

        return $clone->__toString();
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        $data = $this->validateSegment($data, $this->delimiter);
        $data = $this->sanitizeValue($data);

        return array_map('urldecode', $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatRemoveSegment($data)
    {
        return array_map('urldecode', parent::formatRemoveSegment($data));
    }
}

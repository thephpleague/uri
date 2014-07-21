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
        $res = array_values($this->data);
        if (! $res) {
            return null;
        }

        return implode($this->delimiter, array_map('rawurlencode', $res));
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $value = $this->__toString();
        if ('' != $value) {
            $value = '/'.$value;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePath($start_index = 0)
    {
        $start_index = (int) filter_var($start_index, FILTER_VALIDATE_INT, array(
            'options' => array('min_range' => 0)
        ));
        $path = $this->getUriComponent();
        if (0 < $start_index && count($this->data) >= $start_index) {
            $clone = clone $this;
            $clone->set(array_merge(
                array_fill(0, $start_index, '..'),
                array_slice(array_values($clone->toArray()), $start_index)
            ));

            $path = $clone->__toString();
        }

        if ('' == $path) {
            $path = '/'.$path;
        }

        return $path;
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

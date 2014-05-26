<?php

namespace League\Url\Components;

class Path extends AbstractComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data)
    {
        return self::validateSegment($data, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $res = implode('/', str_replace(' ', '%20', $this->data));
        if (empty($res)) {
            $res = null;
        }

        return $res;
    }

    /**
     * Append some data to a given array
     *
     * @param array   $data         the data to append
     * @param string  $whence       the value of the data to prepend before
     * @param integer $whence_index the occurence index for $whence
     */
    public function append($data, $whence = null, $whence_index = null)
    {
        $this->data = self::appendSegment(
            $this->data,
            self::validateSegment($data, '/'),
            $whence,
            $whence_index
        );
    }

    /**
     * Prepend some data to a given array
     *
     * @param array   $data         the data to prepend
     * @param string  $whence       the value of the data to prepend before
     * @param integer $whence_index the occurence index for $whence
     */
    public function prepend($data, $whence = null, $whence_index = null)
    {
        $this->data = self::prependSegment(
            $this->data,
            self::validateSegment($data, '/'),
            $whence,
            $whence_index
        );
    }

    /**
     * Append some data to a given array
     *
     * @param array $data the data to remove
     */
    public function remove($data)
    {
        $data = self::removeSegment($this->data, $data, '/');
        if (! is_null($data)) {
            $this->set($data);
        }
    }
}

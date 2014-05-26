<?php

namespace League\Url\Components;

class Path extends AbstractComponent implements ComponentInterface
{
    protected $delimiter = '/';

    /**
     * {@inheritdoc}
     */
    public function validate($data)
    {
        return $this->validateSegment($data, $this->delimiter);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $res = implode($this->delimiter, str_replace(' ', '%20', $this->data));
        if (empty($res)) {
            $res = '';
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
        $this->data = $this->appendSegment(
            $this->data,
            $this->validateSegment($data, $this->delimiter),
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
        $this->data = $this->prependSegment(
            $this->data,
            $this->validateSegment($data, $this->delimiter),
            $whence,
            $whence_index
        );
    }
}

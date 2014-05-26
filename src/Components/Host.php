<?php

namespace League\Url\Components;

use InvalidArgumentException;

class Host extends AbstractComponent implements ComponentInterface
{

    protected $delimiter = '.';

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
    public function validate($data, array $host = array())
    {
        $data = $this->validateSegment($data, $this->delimiter);
        $imploded = implode($this->delimiter, $data);
        if (127 <= (count($host) + count($data))) {
            throw new InvalidArgumentException('Host may have at maximum 127 parts');
        } elseif (225 <= (strlen(implode($this->delimiter, $host)) + strlen($imploded) + 1)) {
            throw new InvalidArgumentException('Host may have a maximum of 255 characters');
        } elseif (strpos($imploded, ' ') !== false || strpos($imploded, '_') !== false) {
            throw new InvalidArgumentException('Invalid Characters used to create your host');
        }
        foreach ($data as $value) {
            if (strlen($value) > 63) {
                throw new InvalidArgumentException('each label host must have a maximum of 63 characters');
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $res = implode($this->delimiter, $this->data);
        if (empty($res)) {
            $res = '';
        }

        return $res;
    }

    /**
     * Prepend the URL host component
     *
     * @param mixed   $data         the host data can be a array or a string
     * @param string  $whence       where the data should be prepended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
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
     * Append the URL host component
     *
     * @param mixed   $data         the host data can be a array or a string
     * @param string  $whence       where the data should be appended to
     * @param integer $whence_index the recurrence index of $whence
     *
     * @return self
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
}

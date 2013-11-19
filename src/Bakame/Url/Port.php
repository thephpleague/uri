<?php

namespace Bakame\Url;

class Port
{

    private $data = null;

    public function __construct($str = null)
    {
        $this->set($str);
    }

    public function get()
    {
        return $this->data;
    }

    public function set($value = null)
    {
        if (null === $value) {
            $this->data = null;

            return $this;
        }
        $this->data = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'default' => 80]]);

        return $this;
    }

    public function __toString()
    {
        $str = '';
        if (! empty($this->data) && 80 != $this->data) {
            $str = ':'.$this->data;
        }

        return $str;
    }
}

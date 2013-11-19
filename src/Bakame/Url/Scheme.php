<?php

namespace Bakame\Url;

class Scheme
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
        $this->data = filter_var($value, FILTER_SANITIZE_STRING);

        return $this;
    }

    public function __toString()
    {
        $str = $this->data;
        if (! empty($str)) {
            $str .= ':';
        }

        return $str .= '//';
    }
}

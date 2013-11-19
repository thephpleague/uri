<?php

namespace Bakame\Url;

class Fragment
{

    private $data;

    public function __construct($str)
    {
        $this->data = $str;
    }

    public function get()
    {
        return $this->data;
    }

    public function set($value)
    {
        $this->data = $value;

        return $this;
    }

    public function __toString()
    {
        $str = $this->data;
        if (!empty($str)) {
            $str = '#'.$str;
        }

        return $str;
    }
}

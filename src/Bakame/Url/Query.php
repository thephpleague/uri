<?php

namespace Bakame\Url;

class Query
{
    private $data = [];

    public function __construct($query)
    {
        parse_str($query, $res);
        $this->data = $res;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function get($key = null)
    {

        if (null == $key) {
            return $this->data;
        } elseif ($this->has($key)) {
            return $this->data[$key];
        }

        return null;
    }

    public function set($name, $value = null)
    {
        if (! is_array($name)) {
            $name = [$name => $value];
        }
        $this->data = array_filter($name + $this->data, function ($value) {
            return null !== $value;
        });

        return $this;
    }

    public function clear()
    {
        $this->data = [];
    }

    public function __toString()
    {
        $str = http_build_query($this->data);

        if (! empty($str)) {
            $str = '?'.$str;
        }

        return $str;
    }
}

<?php

namespace Bakame\Url;

class Segment
{

    private $separator;

    private $data = [];

    public function __construct($str, $separator)
    {
        $this->separator = $separator;
        if (! is_null($str)) {
            if ($this->separator == $str[0]) {
                $str = substr($str, 1);
            }
            $this->data = explode($this->separator, $str);
        }
    }

    public function has($name)
    {
        return in_array($name, $this->data);
    }

    public function get($index = null)
    {
        if (null === $index) {
            return $this->data;
        } elseif (array_key_exists($index, $this->data)) {
            return $this->data[$index];
        }

        return null;
    }

    public function set($segment, $position = 'append', $segmentBefore = null, $segmentBeforeIndex = null)
    {
        if (! in_array($position, array('append', 'prepend'))) {
            $position = 'append';
        }

        return $this->$position($segment, $segmentBefore, $segmentBeforeIndex);
    }

    public function remove($name)
    {
        $name = (array) $name;
        $this->data = array_filter($this->data, function ($value) use ($name) {
            return ! in_array($value, $name);
        });

        return $this;
    }

    public function clear()
    {
        $this->data = [];

        return $this;
    }

    private function append($segment, $segmentBefore = null, $segmentBeforeIndex = null)
    {
        $new = (array) $segment;
        $old = $this->get();
        $extra = [];
        if (null !== $segmentBefore && count($found = array_keys($old, $segmentBefore))) {
            $index = $found[0];
            if (array_key_exists($segmentBeforeIndex, $found)) {
                $index = $found[$segmentBeforeIndex];
            }
            $extra = array_slice($old, $index+1);
            $old = array_slice($old, 0, $index+1);
        }
        $this->data = array_merge($old, $new, $extra);

        return $this;
    }

    private function prepend($segment, $segmentBefore = null, $segmentBeforeIndex = null)
    {
        $new = (array) $segment;
        $old = $this->get();
        if (null !== $segmentBefore && count($found = array_keys($old, $segmentBefore))) {
            $index = $found[0];
            if (array_key_exists($segmentBeforeIndex, $found)) {
                $index = $found[$segmentBeforeIndex];
            }
            $extra = array_slice($old, $index);
            $old = array_slice($old, 0, $index);
            $this->data = array_merge($old, $new, $extra);

            return $this;
        }
        $this->data = array_merge($new, $old);

        return $this;
    }

    public function __toString()
    {
        return implode($this->separator, $this->data);
    }
}

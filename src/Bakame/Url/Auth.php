<?php

namespace Bakame\Url;

class Auth
{
    private $data = ['user' => null, 'pass' => null];

    public function __construct(array $auth)
    {
        $this->data['user'] = $auth['user'];
        $this->data['pass'] = $auth['pass'];
    }

    public function get($key = null)
    {
        if (null === $key) {
            return $this->data;
        } elseif (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return null;
    }

    public function remove($key)
    {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = null;
        }

        return $this;
    }

    public function clear()
    {
        $this->data = ['user' => null, 'pass' => null];

        return $this;
    }

    public function set($key, $value = null)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }
        foreach ($key as $prop => $value) {
            if (array_key_exists($prop, $this->data)) {
                $this->data[$prop] = $value;
            }
        }

        return $this;
    }

    public function __toString()
    {
        $user = $this->data['user'];
        $pass = $this->data['pass'];
        if (! empty($pass)) {
            $pass = ':'.$pass;
        }

        if ($user || $pass) {
            $pass .= '@';
        }

        return $user.$pass;
    }
}

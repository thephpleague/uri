<?php

namespace League\Url\Components;

interface ComponentInterface
{
    /**
     * Set the component data
     * @param mixed $data data to be added
     */
    public function set($data);

    /**
     * Component validation method Validation
     *
     * @param mixed $data data to be evaluated
     */
    public function validate($data);

    /**
     * String representation of an URL component
     */
    public function __toString();
}

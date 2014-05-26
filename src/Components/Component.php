<?php

namespace League\Url\Components;

class Component extends AbstractStringComponent implements ComponentInterface
{
    public function validate($data)
    {
        return self::sanitizeComponent($data);
    }
}

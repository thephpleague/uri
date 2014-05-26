<?php

namespace League\Url\Components;

class Scheme extends AbstractStringComponent implements ComponentInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data)
    {
        return $this->validateScheme($data);
    }
}

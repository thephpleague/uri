<?php

namespace League\Url\Components;

abstract class AbstractStringComponent extends Validation implements ComponentInterface
{
    /**
     * Query
     * @var array
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function __construct($data = null)
    {
        $this->set($data);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data)
    {
        $this->data = $this->validate($data);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->data;
    }
}

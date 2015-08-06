<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/uri/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.uri
 */
namespace League\Uri\Components;

use League\Uri\Interfaces\Components\Port as PortInterface;

/**
 * Value object representing a URI port component.
 *
 * @package League.uri
 * @since   1.0.0
 */
class Port extends AbstractComponent implements PortInterface
{
    use PortValidatorTrait;

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        return $this->validatePort($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $component = $this->getContent();

        return null === $component ? '' : ':'.$component;
    }

    /**
     * {@inheritdoc}
     */
    public function toInt()
    {
        return $this->data;
    }

    /**
     * Initialize the Port data
     *
     * @param int $data
     */
    protected function init($data)
    {
        $this->data = $this->validate($data);
    }
}

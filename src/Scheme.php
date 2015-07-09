<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri;

use InvalidArgumentException;

/**
 * Value object representing a URL scheme component.
 *
 * @package League.url
 * @since 1.0.0
 */
class Scheme extends AbstractComponent implements Interfaces\Scheme
{
    /**
     * new instance
     *
     * @param string $data the component value
     */
    public function __construct($data = '')
    {
        $data = $this->validateString($data);
        if (!empty($data)) {
            $this->data = $this->validate($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        if (!preg_match('/^[a-z][-a-z0-9+.]+$/i', $data)) {
            throw new InvalidArgumentException(sprintf("Invalid Submitted scheme: '%s'", $data));
        }

        return strtolower($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $data = $this->__toString();
        if (empty($data)) {
            return $data;
        }
        return $data.':';
    }
}

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

use League\Uri\Interfaces\Components\Fragment as FragmentInterface;

/**
 * Value object representing a URI fragment component.
 *
 * @package League.uri
 * @since   1.0.0
 */
class Fragment extends AbstractComponent implements FragmentInterface
{
    /**
     * {@inheritdoc}
     */
    protected static $characters_set = [
        '!', '$', '&', "'", '(', ')', '*', '+',
        ',', ';', '=', ':', '@', '/', '?',
    ];

    /**
     * {@inheritdoc}
     */
    protected static $characters_set_encoded = [
        '%21', '%24', '%26', '%27', '%28', '%29', '%2A', '%2B',
        '%2C', '%3B', '%3D', '%3A', '%40', '%2F', '%3F',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $component = $this->getContent();

        return null === $component ? '' : '#'.$component;
    }
}

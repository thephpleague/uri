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
namespace League\Url;

/**
 * Value object representing a URL fragment component.
 *
 * @package League.url
 * @since   1.0.0
 */
class Fragment extends AbstractComponent implements Interfaces\Fragment
{
    /**
     * {@inheritdoc}
     */
    protected static $characters_set = [
        "!", "$", "&", "'", "(", ")", "*", "+",
        ",", ";", "=", ":", "@", "/", "?",
    ];

    /**
     * {@inheritdoc}
     */
    protected static $characters_set_encoded = [
        "%21", "%24", "%26", "%27", "%28", "%29", "%2A", "%2B",
        "%2C", "%3B", "%3D", "%3A", "%40", "%2F", "%3F"
    ];

    /**
     * {@inheritdoc}
     */
    public function getUriComponent()
    {
        $data = $this->__toString();
        if (!empty($data)) {
            return '#'.$data;
        }

        return $data;
    }
}

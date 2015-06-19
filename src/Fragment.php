<?php
/**
 * This file is part of the League.url library
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/thephpleague/url/
 * @version 4.0.0
 * @package League.url
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Url;

/**
 * Value object representing a URL fragment component.
 *
 * @package League.url
 * @since 1.0.0
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

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
namespace League\Url\Utilities;

use InvalidArgumentException;
use League\Url\Interfaces;

/**
 * A trait with common methods for Component Value Object
 *
 * @package League.url
 * @since 4.0.0
 */
trait ComponentTrait
{
    /**
     * Characters to conform to RFC3986 - http://tools.ietf.org/html/rfc3986#section-2
     *
     * @var array
     */
    protected static $characters_set = [
        "!", "$", "&", "'", "(", ")", "*", "+", ",", ";", "=", ":",
    ];

    /**
     * Encoded characters to conform to RFC3986 - http://tools.ietf.org/html/rfc3986#section-2
     *
     * @var array
     */
    protected static $characters_set_encoded = [
        "%21", "%24", "%26", "%27", "%28", "%29", "%2A", "%2B", "%2C", "%3B", "%3D", "%3A",
    ];


    /**
     * {@inheritdoc}
     */
    public abstract function getUriComponent();

    /**
     * validate a string
     *
     * @param  mixed $str
     *
     * @throws \InvalidArgumentException if the submitted data can not be converted to string
     *
     * @return string
     */
    protected function validateString($str)
    {
        if (is_bool($str) || is_array($str)) {
            throw new InvalidArgumentException(
                'Data passed must be stringable into a string; received "'.gettype($str).'"'
            );
        }

        $str = (string) $str;

        return trim($str);
    }

    /**
     * {@inheritdoc}
     */
    public function sameValueAs(Interfaces\UrlPart $component)
    {
        return $component->getUriComponent() === $this->getUriComponent();
    }

    /**
     * Encoding string according to RFC3986
     *
     * @param  string $value
     *
     * @return string
     */
    protected function encode($value)
    {
        return str_replace(static::$characters_set_encoded, static::$characters_set, rawurlencode($value));
    }
}

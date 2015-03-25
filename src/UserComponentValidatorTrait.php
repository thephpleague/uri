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

use InvalidArgumentException;

/**
 *
 * A class to manipulate an URL as a Value Object
 *
 * @package League.url
 * @since 4.0.0
 */
trait UserComponentValidatorTrait
{
    /**
     * {@inheritdoc}
     */
    protected function validate($data)
    {
        // https://tools.ietf.org/html/rfc3986#section-3.2.1
        // userinfo    = *( unreserved / pct-encoded / sub-delims / ":" )

        if (ctype_alnum($data)) {
            // Simplest and most common use case: component is alphanumeric.
            return $data;
        }

        // unreserved  = ALPHA / DIGIT / "-" / "." / "_" / "~"
        // pct-encoded = "%" HEXDIG HEXDIG
        // sub-delims  = "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="

        // Anything percent-encoded is safe and can be removed.
        $component = preg_replace('/%[0-9a-f]{2}/i', '', $data);

        // With what remains, ensure that it is "unreserved" or "sub-delimiter".
        $unreserved = '-a-z0-9._~';
        $subdelims  = preg_quote('!$&\'()*+,;=]/', '/');

        if (! preg_match('/^[' . $unreserved . $subdelims . ']+$/i', $component)) {
            throw new InvalidArgumentException('The submitted user info is invalid');
        }

        return $data;
    }
}

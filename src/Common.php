<?php

/**
 * League.Uri (https://uri.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Uri;

use League\Uri\Exceptions\IdnSupportMissing;
use League\Uri\Exceptions\SyntaxError;

final class Common
{
    /**
     * Range of invalid characters in URI string.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2.2
     *
     * @var string
     */
    public const REGEXP_INVALID_URI_CHARS = '/[\x00-\x1f\x7f]/';

    /**
     * Invalid characters in host regular expression pattern.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2.2
     */
    public const REGEXP_INVALID_HOST_CHARS = '/
        [:\/?#\[\]@ ]  # gen-delims characters as well as the space character
    /ix';

    /**
     * RFC3986 IPvFuture regular expression pattern.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @var string
     */
    public const REGEXP_HOST_IPFUTURE = '/^
        v(?<version>[A-F0-9])+\.
        (?:
            (?<unreserved>[a-z0-9_~\-\.])|
            (?<sub_delims>[!$&\'()*+,;=:])  # also include the : character
        )+
    $/ix';

    /**
     * RFC3986 host identified by a registered name regular expression pattern.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @var string
     */
    public const REGEXP_HOST_REGISTERED_NAME = '/(?(DEFINE)
        (?<unreserved>[a-z0-9_~\-])   # . is missing as it is used to separate labels
        (?<sub_delims>[!$&\'()*+,;=])
        (?<encoded>%[A-F0-9]{2})
        (?<reg_name>(?:(?&unreserved)|(?&sub_delims)|(?&encoded))*)
    )
    ^(?:(?&reg_name)\.)*(?&reg_name)\.?$/ix';

    /**
     * Only the address block fe80::/10 can have a Zone ID attach to
     * let's detect the link local significant 10 bits.
     */
    public const ZONE_ID_ADDRESS_BLOCK = "\xfe\x80";

    /**
     * IDNA errors.
     *
     * @var array
     */
    private const IDNA_ERRORS = [
        IDNA_ERROR_EMPTY_LABEL => 'a non-final domain name label (or the whole domain name) is empty',
        IDNA_ERROR_LABEL_TOO_LONG => 'a domain name label is longer than 63 bytes',
        IDNA_ERROR_DOMAIN_NAME_TOO_LONG => 'a domain name is longer than 255 bytes in its storage form',
        IDNA_ERROR_LEADING_HYPHEN => 'a label starts with a hyphen-minus ("-")',
        IDNA_ERROR_TRAILING_HYPHEN => 'a label ends with a hyphen-minus ("-")',
        IDNA_ERROR_HYPHEN_3_4 => 'a label contains hyphen-minus ("-") in the third and fourth positions',
        IDNA_ERROR_LEADING_COMBINING_MARK => 'a label starts with a combining mark',
        IDNA_ERROR_DISALLOWED => 'a label or domain name contains disallowed characters',
        IDNA_ERROR_PUNYCODE => 'a label starts with "xn--" but does not contain valid Punycode',
        IDNA_ERROR_LABEL_HAS_DOT => 'a label contains a dot=full stop',
        IDNA_ERROR_INVALID_ACE_LABEL => 'An ACE label does not contain a valid label string',
        IDNA_ERROR_BIDI => 'a label does not meet the IDNA BiDi requirements (for right-to-left characters)',
        IDNA_ERROR_CONTEXTJ => 'a label does not meet the IDNA CONTEXTJ requirements',
    ];

    /**
     * Retrieves and format IDNA conversion error message.
     *
     * @see http://icu-project.org/apiref/icu4j/com/ibm/icu/text/IDNA.Error.html
     */
    public static function getIDNAErrors(int $error_byte): string
    {
        $res = [];

        foreach (self::IDNA_ERRORS as $error => $reason) {
            if ($error === ($error_byte & $error)) {
                $res[] = $reason;
            }
        }

        return [] === $res ? 'Unknown IDNA conversion error.' : implode(', ', $res).'.';
    }

    /**
     * Validate and format a registered name.
     *
     * The host is converted to its ascii representation if needed
     *
     * @throws IdnSupportMissing if the submitted host required missing or misconfigured IDN support
     * @throws SyntaxError       if the submitted host is not a valid registered name
     */
    public static function filterRegisteredName(string $host, bool $format = true): string
    {
        $formatted_host = rawurldecode($host);

        if (1 === preg_match(Common::REGEXP_HOST_REGISTERED_NAME, $formatted_host)) {
            if (false === strpos($formatted_host, 'xn--')) {
                return $format ? strtolower($formatted_host) : $host;
            }

            self::checkIDN($host);

            return $format ? strtolower($host) : $host;
        }

        if (1 === preg_match(Common::REGEXP_INVALID_HOST_CHARS, $formatted_host)) {
            throw new SyntaxError(sprintf('The host `%s` is invalid : a registered name can not contain URI delimiters or spaces', $host));
        }

        $idn = self::checkIDN($formatted_host, false);

        if (false !== strpos($idn, '%')) {
            throw new SyntaxError(sprintf('Host `%s` is invalid : the host is not a valid registered name', $host));
        }

        return $format ? $idn : $host;
    }

    /**
     * Filter IDN domain to UTF8 or ASCII.
     *
     * @throws SyntaxError
     * @throws IdnSupportMissing
     */
    private static function checkIDN(string $host, bool $uft8 = true): string
    {
        self::checkIDNSupport($host);

        if ($uft8) {
            $host = strtolower($host);
            $convert = idn_to_utf8(
                $host,
                IDNA_CHECK_BIDI | IDNA_CHECK_CONTEXTJ | IDNA_NONTRANSITIONAL_TO_UNICODE,
                INTL_IDNA_VARIANT_UTS46,
                $idna_info
            );
        } else {
            $convert = idn_to_ascii(
                $host,
                IDNA_CHECK_BIDI | IDNA_CHECK_CONTEXTJ | IDNA_NONTRANSITIONAL_TO_ASCII,
                INTL_IDNA_VARIANT_UTS46,
                $idna_info
            );
        }

        if (0 !== $idna_info['errors']) {
            throw new SyntaxError(sprintf('The host `%s` is invalid : %s', $host, Common::getIDNAErrors($idna_info['errors'])));
        }

        // @codeCoverageIgnoreStart
        // added because it is not possible in travis to disabled the ext/intl extension
        // see travis issue https://github.com/travis-ci/travis-ci/issues/4701
        if (false === $convert) {
            throw new IdnSupportMissing(sprintf('The Intl extension is misconfigured for %s, please correct this issue before proceeding.', PHP_OS));
        }
        // @codeCoverageIgnoreEnd

        return $idna_info['result'] ?? '';
    }

    /**
     * Check IDN support.
     *
     * @codeCoverageIgnore
     * added because it is not possible in travis to disabled the ext/intl extension
     * see travis issue https://github.com/travis-ci/travis-ci/issues/4701
     *
     * @throws IdnSupportMissing
     */
    private static function checkIDNSupport(string $host): void
    {
        static $idn_support = null;
        $idn_support = $idn_support ?? function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46');

        if (!$idn_support) {
            throw new IdnSupportMissing(sprintf('The host `%s` could not be processed for IDN. Verify that ext/intl is installed for IDN support and that ICU is at least version 4.6.', $host));
        }
    }
}

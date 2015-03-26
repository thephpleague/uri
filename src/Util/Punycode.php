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
namespace League\Url\Util;

/**
 * Punycode implementation as described in RFC 3492
 *
 * @link http://tools.ietf.org/html/rfc3492
 *
 * This is a fork from https://github.com/true/php-punycode/
 * Created by TrueServer B.V.
 *
 */
trait Punycode
{
    /**
     * Character encoding
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Encode table
     *
     * @param array
     */
    protected static $encodeTable = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
        'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
        'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
    ];

    /**
     * Decode table
     *
     * @param array
     */
    protected static $decodeTable = [];

    /**
     * Encode a part of a domain name, such as tld, to its Punycode version
     *
     * @param string $input Part of a domain name
     * @return string Punycode representation of a domain part
     */
    protected function encodeLabel($input)
    {
        $codePoints = $this->codePoints($input);

        $n = static::INITIAL_N;
        $bias = static::INITIAL_BIAS;
        $delta = 0;
        $h = $b = count($codePoints['basic']);

        $output = '';
        foreach ($codePoints['basic'] as $code) {
            $output .= $this->codePointToChar($code);
        }
        if ($input === $output) {
            return $output;
        }
        if ($b > 0) {
            $output .= static::DELIMITER;
        }

        $i = 0;
        $length = mb_strlen($input, $this->encoding);
        while ($h < $length) {
            $m = $codePoints['nonBasic'][$i++];
            $delta = $delta + ($m - $n) * ($h + 1);
            $n = $m;

            foreach ($codePoints['all'] as $c) {
                if ($c < $n || $c < static::INITIAL_N) {
                    $delta++;
                }
                if ($c === $n) {
                    $q = $delta;
                    for ($k = static::BASE;; $k += static::BASE) {
                        $t = $this->calculateThreshold($k, $bias);
                        if ($q < $t) {
                            break;
                        }

                        $code = $t + (($q - $t) % (static::BASE - $t));
                        $output .= static::$encodeTable[$code];

                        $q = ($q - $t) / (static::BASE - $t);
                    }

                    $output .= static::$encodeTable[$q];
                    $bias = $this->adapt($delta, $h + 1, ($h === $b));
                    $delta = 0;
                    $h++;
                }
            }

            $delta++;
            $n++;
        }

        return static::PREFIX.$output;
    }

    /**
     * Decode a part of domain name, such as tld
     *
     * @param string $input Part of a domain name
     * @return string Unicode domain part
     */
    protected function decodeLabel($input)
    {
        if (empty(static::$decodeTable)) {
            static::$decodeTable = array_flip(static::$encodeTable);
        }

        $n = static::INITIAL_N;
        $i = 0;
        $bias = static::INITIAL_BIAS;
        $output = '';

        $pos = strrpos($input, static::DELIMITER);
        if ($pos !== false) {
            $output = substr($input, 0, $pos++);
        } else {
            $pos = 0;
        }

        $outputLength = strlen($output);
        $inputLength = strlen($input);
        while ($pos < $inputLength) {
            $oldi = $i;
            $w = 1;

            for ($k = static::BASE;; $k += static::BASE) {
                $digit = static::$decodeTable[$input[$pos++]];
                $i = $i + ($digit * $w);
                $t = $this->calculateThreshold($k, $bias);

                if ($digit < $t) {
                    break;
                }

                $w = $w * (static::BASE - $t);
            }

            $bias = $this->adapt($i - $oldi, ++$outputLength, ($oldi === 0));
            $n = $n + (int) ($i / $outputLength);
            $i = $i % ($outputLength);
            $output = mb_substr($output, 0, $i, $this->encoding)
                .$this->codePointToChar($n)
                .mb_substr($output, $i, $outputLength - 1, $this->encoding);

            $i++;
        }

        return $output;
    }

    /**
     * Calculate the bias threshold to fall between TMIN and TMAX
     *
     * @param int $k
     * @param int $bias
     * @return int
     */
    protected function calculateThreshold($k, $bias)
    {
        if ($k <= $bias + static::TMIN) {
            return static::TMIN;
        }

        if ($k >= $bias + static::TMAX) {
            return static::TMAX;
        }

        return $k - $bias;
    }

    /**
     * Bias adaptation
     *
     * @param int $delta
     * @param int $numPoints
     * @param boolean $firstTime
     * @return int
     */
    protected function adapt($delta, $numPoints, $firstTime)
    {
        $key   = 0;
        $delta = $firstTime ? floor($delta / static::DAMP) : $delta >> 1;
        $delta += floor($delta / $numPoints);

        $tmp = static::BASE - static::TMIN;
        for (; $delta > $tmp * static::TMAX >> 1; $key += static::BASE) {
            $delta = floor($delta / $tmp);
        }

        return floor($key + ($tmp + 1) * $delta / ($delta + static::SKEW));
    }

    /**
     * List code points for a given input
     *
     * @param string $input
     * @return array Multi-dimension array with basic, non-basic and aggregated code points
     */
    protected function codePoints($input)
    {
        $codePoints = [
            'all'      => [],
            'basic'    => [],
            'nonBasic' => [],
        ];

        $length = mb_strlen($input, $this->encoding);
        for ($i = 0; $i < $length; $i++) {
            $code = $this->charToCodePoint(mb_substr($input, $i, 1, $this->encoding));
            $codePoints['all'][] = $code;
            $key = 'nonBasic';
            if ($code < 128) {
                $key = 'basic';
            }
            $codePoints[$key][] = $code;
        }

        $codePoints['nonBasic'] = array_unique($codePoints['nonBasic']);
        sort($codePoints['nonBasic']);

        return $codePoints;
    }

    /**
     * Convert a single or multi-byte character to its code point
     *
     * @param string $char
     * @return int
     */
    protected function charToCodePoint($char)
    {
        $code = ord($char[0]);
        if ($code < 128) {
            return $code;
        }

        if ($code < 224) {
            return (($code - 192) * 64) + (ord($char[1]) - 128);
        }

        if ($code < 240) {
            return (($code - 224) * 4096)
                + ((ord($char[1]) - 128) * 64)
                + (ord($char[2]) - 128);
        }

        return (($code - 240) * 262144)
            + ((ord($char[1]) - 128) * 4096)
            + ((ord($char[2]) - 128) * 64)
            + (ord($char[3]) - 128);
    }

    /**
     * Convert a code point to its single or multi-byte character
     *
     * @param int $code
     * @return string
     */
    protected function codePointToChar($code)
    {
        if ($code <= 0x7F) {
            return chr($code);
        }

        if ($code <= 0x7FF) {
            return chr(($code >> 6) + 192).chr(($code & 63) + 128);
        }

        if ($code <= 0xFFFF) {
            return chr(($code >> 12) + 224)
                .chr((($code >> 6) & 63) + 128)
                .chr(($code & 63) + 128);
        }

        return chr(($code >> 18) + 240)
            .chr((($code >> 12) & 63) + 128)
            .chr((($code >> 6) & 63) + 128)
            .chr(($code & 63) + 128);
    }
}

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

/**
 * Punycode implementation as described in RFC 3492
 *
 * @link http://tools.ietf.org/html/rfc3492
 * @package League.url
 * @since 4.0.0
 *
 * This is a fork from https://github.com/true/php-punycode/
 * Created by TrueServer B.V.
 *
 */
trait Punycode
{
    /**
     * Encode table
     *
     * @param array
     */
    protected static $encodeTable = [
        0  => 'a', 1  => 'b', 2  => 'c', 3  => 'd', 4  => 'e', 5  => 'f',
        6  => 'g', 7  => 'h', 8  => 'i', 9  => 'j', 10 => 'k', 11 => 'l',
        12 => 'm', 13 => 'n', 14 => 'o', 15 => 'p', 16 => 'q', 17 => 'r',
        18 => 's', 19 => 't', 20 => 'u', 21 => 'v', 22 => 'w', 23 => 'x',
        24 => 'y', 25 => 'z', 26 =>   0, 27 =>   1, 28 =>   2, 29 =>   3,
        30 =>   4, 31 =>   5, 32 =>   6, 33 =>   7, 34 =>   8, 35 =>   9,
    ];

    /**
     * Decode table
     *
     * @param array
     */
    protected static $decodeTable = [
        'a' =>  0, 'b' =>  1, 'c' =>  2, 'd' =>  3, 'e' =>  4, 'f' => 5,
        'g' =>  6, 'h' =>  7, 'i' =>  8, 'j' =>  9, 'k' => 10, 'l' => 11,
        'm' => 12, 'n' => 13, 'o' => 14, 'p' => 15, 'q' => 16, 'r' => 17,
        's' => 18, 't' => 19, 'u' => 20, 'v' => 21, 'w' => 22, 'x' => 23,
        'y' => 24, 'z' => 25,   0 => 26,   1 => 27,   2 => 28,   3 => 29,
          4 => 30,   5 => 31,   6 => 32,   7 => 33,   8 => 34,   9 => 35,
    ];

    /**
     * Encode a hostname label to its Punycode version
     *
     * @param string $input hostname label
     *
     * @return string hostname label punycode representation
     */
    public static function encodeLabel($input)
    {
        return function_exists('idn_to_ascii') ? idn_to_ascii($input) : static::toAscii($input);
    }

    /**
     * Return a punycoded host label into its unicode representation
     *
     * @param  string $input
     *
     * @return string
     */
    public static function decodeLabel($input)
    {
        return function_exists('idn_to_utf8') ? idn_to_utf8($input) : static::toUtf8($input);
    }

    /**
     * Encode a hostname label to its Punycode version
     *
     * @param string $input hostname label
     *
     * @return string hostname label punycode representation
     */
    protected static function toAscii($input)
    {
        $codePoints = static::codePoints($input);
        if (empty($codePoints['nonBasic'])) {
            return $input;
        }

        return static::encodeString($codePoints, $input);
    }

    /**
     * Return a punycoded host label into its unicode representation
     *
     * @param  string $input
     *
     * @return string
     */
    protected static function toUtf8($input)
    {
        if (strpos($input, static::PREFIX) !== 0) {
            return $input;
        }

        $nonBasic = static::codePoints($input)["nonBasic"];
        if (!empty($nonBasic) || !($decoded = static::decodeString(substr($input, strlen(static::PREFIX))))) {
            return $input;
        }

        return $decoded;
    }

    /**
     * List code points for a given input
     *
     * @param string $input
     *
     * @return array Multi-dimension array with basic, non-basic and aggregated code points
     */
    protected static function codePoints($input)
    {
        $codePoints = ['all' => [], 'basic' => [], 'nonBasic' => []];
        $codePoints['all'] = array_map(
            [get_called_class(), 'charToCodePoint'],
            preg_split("//u", $input, -1, PREG_SPLIT_NO_EMPTY)
        );

        foreach ($codePoints['all'] as $code) {
            $codePoints[($code < 128) ? 'basic' : 'nonBasic'][] = $code;
        }

        $codePoints['nonBasic'] = array_unique($codePoints['nonBasic']);
        sort($codePoints['nonBasic']);

        return $codePoints;
    }

    /**
     * Convert a single or multi-byte character to its code point
     *
     * @param string $char
     *
     * @return int
     */
    protected static function charToCodePoint($char)
    {
        $code = ord($char[0]);
        if ($code < 128) {
            return $code;
        }

        if ($code < 224) {
            return (($code - 192) * 64) + (ord($char[1]) - 128);
        }

        if ($code < 240) {
            return (($code - 224) * 4096) + ((ord($char[1]) - 128) * 64) + (ord($char[2]) - 128);
        }

        return (($code - 240) * 262144) + ((ord($char[1]) - 128) * 4096)
            + ((ord($char[2]) - 128) * 64) + (ord($char[3]) - 128);
    }

    /**
     * Encode a string into its punycode version
     *
     * @param  array  $codePoints input code points
     * @param  string $input      input string
     *
     * @return string
     */
    protected static function encodeString(array $codePoints, $input)
    {
        $n      = static::INITIAL_N;
        $bias   = static::INITIAL_BIAS;
        $delta  = 0;
        $h      = count($codePoints['basic']);
        $b      = $h;
        $i      = 0;
        $length = mb_strlen($input, 'UTF-8');
        $output = array_map([get_called_class(), 'codePointToChar'], $codePoints['basic']);
        if ($b > 0) {
            $output[] = static::DELIMITER;
        }
        while ($h < $length) {
            $m     = $codePoints['nonBasic'][$i++];
            $delta = $delta + ($m - $n) * ($h + 1);
            $n     = $m;
            foreach ($codePoints['all'] as $c) {
                if ($c < $n || $c < static::INITIAL_N) {
                    ++$delta;
                }
                if ($c === $n) {
                    $q = $delta;
                    for ($k = static::BASE;; $k += static::BASE) {
                        $t = static::calculateThreshold($k, $bias);
                        if ($q < $t) {
                            break;
                        }
                        $code     = $t + (($q - $t) % (static::BASE - $t));
                        $output[] = static::$encodeTable[$code];
                        $q        = ($q - $t) / (static::BASE - $t);
                    }
                    $output[] = static::$encodeTable[$q];
                    $bias     = static::adapt($delta, $h + 1, $h === $b);
                    $delta    = 0;
                    $h++;
                }
            }
            ++$delta;
            ++$n;
        }

        return static::PREFIX.implode('', $output);
    }

    /**
     * Convert a code point to its single or multi-byte character
     *
     * @param int $code
     *
     * @return string
     */
    protected static function codePointToChar($code)
    {
        if ($code <= 0x7F) {
            return chr($code);
        }

        if ($code <= 0x7FF) {
            return chr(($code >> 6) + 192).chr(($code & 63) + 128);
        }

        if ($code <= 0xFFFF) {
            return chr(($code >> 12) + 224).chr((($code >> 6) & 63) + 128).chr(($code & 63) + 128);
        }

        return chr(($code >> 18) + 240).chr((($code >> 12) & 63) + 128)
            .chr((($code >> 6) & 63) + 128).chr(($code & 63) + 128);
    }

    /**
     * Calculate the bias threshold to fall between TMIN and TMAX
     *
     * @param int $k
     * @param int $bias
     *
     * @return int
     */
    protected static function calculateThreshold($k, $bias)
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
     * @param int     $delta
     * @param int     $numPoints
     * @param boolean $firstTime
     *
     * @return int
     */
    protected static function adapt($delta, $numPoints, $firstTime)
    {
        $offset = 0;
        $delta  = $firstTime ? floor($delta / static::DAMP) : $delta >> 1;
        $delta += floor($delta / $numPoints);

        $tmp = static::BASE - static::TMIN;
        for (; $delta > $tmp * static::TMAX >> 1; $offset += static::BASE) {
            $delta = floor($delta / $tmp);
        }

        return floor($offset + ($tmp + 1) * $delta / ($delta + static::SKEW));
    }

    /**
     * Decode a punycode encoded hostname label
     *
     * @param string $input the punycode encoded label
     *
     * @return string|false Unicode hostname
     */
    protected static function decodeString($input)
    {
        $output = [];
        $pos    = strrpos($input, static::DELIMITER);
        if ($pos !== false) {
            $output = str_split(substr($input, 0, $pos++));
        }
        $pos = (int) $pos;
        $outputLength = count($output);
        $inputLength  = strlen($input);
        $n    = static::INITIAL_N;
        $i    = 0;
        $bias = static::INITIAL_BIAS;
        while ($pos < $inputLength) {
            for ($oldi = $i, $w = 1, $k = static::BASE;; $k += static::BASE) {
                if ($pos >= $inputLength) {
                    return false;
                }
                $digit = static::$decodeTable[$input[$pos++]];
                $i    += $digit * $w;
                $t     = static::calculateThreshold($k, $bias);
                if ($digit < $t) {
                    break;
                }
                $w    *= (static::BASE - $t);
            }
            $bias = static::adapt($i - $oldi, ++$outputLength, $oldi === 0);
            $n    = $n + floor($i / $outputLength);
            $i   %= $outputLength;
            $code = static::codePointToChar($n);
            if (!mb_check_encoding($code, 'UTF-8')) {
                return false;
            }
            $output = array_merge(array_slice($output, 0, $i), [$code], array_slice($output, $i));
            $i++;
        }

        return implode('', $output);
    }
}

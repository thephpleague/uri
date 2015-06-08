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
    protected static $encodeTable = [];

    /**
     * Decode table
     *
     * @param array
     */
    protected static $decodeTable = [];

    /**
     * Initialize encoding/decoding Table
     */
    protected static function initTable()
    {
        if (empty(static::$encodeTable)) {
            static::$encodeTable = array_merge(range('a', 'z'), range(0, 9));
        }

        if (empty(static::$decodeTable)) {
            static::$decodeTable = array_flip(static::$encodeTable);
        }
    }

    /**
     * Encode a hostname label to its Punycode version
     *
     * @param string $input hostname label
     *
     * @return string hostname label punycode representation
     */
    public static function encodeLabel($input)
    {
        $codePoints = static::codePoints($input);
        if (empty($codePoints['nonBasic'])) {
            return $input;
        }

        return static::encodeString($codePoints, $input);
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
        static::initTable();
        $n      = static::INITIAL_N;
        $bias   = static::INITIAL_BIAS;
        $delta  = 0;
        $h      = count($codePoints['basic']);
        $b      = $h;
        $i      = 0;
        $length = mb_strlen($input, 'UTF-8');
        $output = implode('', array_map([get_called_class(), 'codePointToChar'], $codePoints['basic']));
        if ($b > 0) {
            $output .= static::DELIMITER;
        }

        while ($h < $length) {
            $m     = $codePoints['nonBasic'][$i++];
            $delta = $delta + ($m - $n) * ($h + 1);
            $n     = $m;
            foreach ($codePoints['all'] as $c) {
                if ($c < $n || $c < static::INITIAL_N) {
                    $delta++;
                }
                if ($c === $n) {
                    $q = $delta;
                    for ($k = static::BASE;; $k += static::BASE) {
                        $t = static::calculateThreshold($k, $bias);
                        if ($q < $t) {
                            break;
                        }
                        $code    = $t + (($q - $t) % (static::BASE - $t));
                        $output .= static::$encodeTable[$code];
                        $q       = ($q - $t) / (static::BASE - $t);
                    }

                    $output .= static::$encodeTable[$q];
                    $bias    = static::adapt($delta, $h + 1, ($h === $b));
                    $delta   = 0;
                    $h++;
                }
            }
            $delta++;
            $n++;
        }

        return static::PREFIX.$output;
    }

    /**
     * Is a submitted label a valid punycoded label
     *
     * @param  string  $input
     *
     * @return boolean  return true if a valid label is submitted
     */
    public static function decodeLabel($input)
    {
        $decoded = static::decodeString($input);
        if (static::encodeLabel($decoded) == $input) {
            return $decoded;
        }

        return $input;
    }

    /**
     * Decode a punycode encoded hostname label
     *
     * @param string $input the punycode encoded label
     *
     * @return string Unicode hostname
     */
    protected static function decodeString($input)
    {
        if (strpos($input, static::PREFIX) !== 0) {
            return $input;
        }
        $input  = substr($input, strlen(static::PREFIX));
        $output = '';
        $pos    = strrpos($input, static::DELIMITER);
        if ($pos !== false) {
            $output = substr($input, 0, $pos++);
        }
        $outputLength = strlen($output);
        $inputLength  = strlen($input);
        static::initTable();
        $n    = static::INITIAL_N;
        $i    = 0;
        $bias = static::INITIAL_BIAS;
        $pos  = (int) $pos;
        while ($pos < $inputLength) {
            for ($oldi = $i, $w = 1, $k = static::BASE;; $k += static::BASE) {
                $digit = static::$decodeTable[$input[$pos++]];
                $i     = $i + ($digit * $w);
                $t     = static::calculateThreshold($k, $bias);
                if ($digit < $t) {
                    break;
                }
                $w = $w * (static::BASE - $t);
            }

            $bias = static::adapt($i - $oldi, ++$outputLength, ($oldi === 0));
            $n    = $n + (int) ($i / $outputLength);
            $i    = $i % ($outputLength);
            $output = mb_substr($output, 0, $i, 'UTF-8')
                .static::codePointToChar($n)
                .mb_substr($output, $i, $outputLength - 1, 'UTF-8');
            $i++;
        }

        return $output;
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
}

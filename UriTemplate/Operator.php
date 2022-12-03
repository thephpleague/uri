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

namespace League\Uri\UriTemplate;

use League\Uri\Exceptions\SyntaxError;
use function preg_match;
use function rawurlencode;
use function str_contains;
use function str_replace;

/**
 * Processing behavior according to the expression type operator.
 *
 * @internal The class exposes the internal representation of an Operator and its usage
 * @link https://tools.ietf.org/html/rfc6570#appendix-A
 */
enum Operator: string
{
    /**
     * Expression regular expression pattern.
     *
     * @link https://tools.ietf.org/html/rfc6570#section-2.2
     */
    private const REGEXP_EXPRESSION = '/^\{(?:(?<operator>[\.\/;\?&\=,\!@\|\+#])?(?<variables>[^\}]*))\}$/';

    /**
     * Reserved Operator characters.
     *
     * @link https://tools.ietf.org/html/rfc6570#section-2.2
     */
    private const RESERVED_OPERATOR = '=,!@|';

    case Space = '';
    case Plus = '+';
    case Dot = '.';
    case Slash = '/';
    case Semicolon = ';';
    case QuestionMark = '?';
    case Ampersand = '&';
    case Dash = '#';

    public function first(): string
    {
        return match ($this) {
            self::Space, self::Plus => '',
            default => $this->value,
        };
    }

    public function separator(): string
    {
        return match ($this) {
            self::Space, self::Plus, self::Dash => ',',
            self::QuestionMark, self::Ampersand => '&',
            default => $this->value,
        };
    }

    public function isNamed(): bool
    {
        return match ($this) {
            self::QuestionMark, self::Semicolon, self::Ampersand => true,
            default => false,
        };
    }

    /**
     * Removes percent encoding on reserved characters (used with + and # modifiers).
     */
    public function decode(string $var): string
    {
        static $delimiters = [
            ':', '/', '?', '#', '[', ']', '@', '!', '$',
            '&', '\'', '(', ')', '*', '+', ',', ';', '=',
        ];

        static $delimitersEncoded = [
            '%3A', '%2F', '%3F', '%23', '%5B', '%5D', '%40', '%21', '%24',
            '%26', '%27', '%28', '%29', '%2A', '%2B', '%2C', '%3B', '%3D',
        ];

        return match ($this) {
            Operator::Plus, Operator::Dash => str_replace($delimitersEncoded, $delimiters, rawurlencode($var)),
            default => rawurlencode($var),
        };
    }

    /**
     * @return array{operator:Operator, variables:string}
     */
    public static function parseExpression(string $expression): array
    {
        if (1 !== preg_match(self::REGEXP_EXPRESSION, $expression, $parts)) {
            throw new SyntaxError('The expression "'.$expression.'" is invalid.');
        }

        /** @var array{operator:string, variables:string} $parts */
        $parts = $parts + ['operator' => ''];
        if ('' !== $parts['operator'] && str_contains(self::RESERVED_OPERATOR, $parts['operator'])) {
            throw new SyntaxError('The operator used in the expression "'.$expression.'" is reserved.');
        }

        return [
            'operator' => self::from($parts['operator']),
            'variables' => $parts['variables'],
        ];
    }
}
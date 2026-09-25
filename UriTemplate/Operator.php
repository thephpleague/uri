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

use League\Uri\Encoder;
use League\Uri\Exceptions\SyntaxError;
use Stringable;

use function array_pad;
use function dd;
use function explode;
use function implode;
use function is_array;
use function mb_substr;
use function preg_match;
use function rawurldecode;
use function rawurlencode;
use function str_contains;

/**
 * Processing behavior according to the expression type operator.
 *
 * @internal The class exposes the internal representation of an Operator and its usage
 *
 * @link https://www.rfc-editor.org/rfc/rfc6570#section-2.2
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

    case None = '';
    case ReservedChars = '+';
    case Label = '.';
    case Path = '/';
    case PathParam = ';';
    case Query = '?';
    case QueryPair = '&';
    case Fragment = '#';

    public function nextDelimiter(): ?string
    {
        return match ($this) {
            self::Query,
            self::QueryPair,
            self::ReservedChars => '#',
            self::Fragment => null,
            default => '?#',
        };
    }

    public function supportsNamedListValue(): bool
    {
        return match ($this) {
            self::PathParam,
            self::Fragment => true,
            default => false,
        };
    }

    public function first(): string
    {
        return match ($this) {
            self::None, self::ReservedChars => '',
            default => $this->value,
        };
    }

    public function separator(): string
    {
        return match ($this) {
            self::None, self::ReservedChars, self::Fragment => ',',
            self::Query, self::QueryPair => '&',
            default => $this->value,
        };
    }

    public function supportsListValue(): bool
    {
        return match ($this) {
            self::None,
            self::Path,
            self::PathParam,
            self::Query,
            self::QueryPair => true,
            default => false,
        };
    }

    public function isNamed(): bool
    {
        return match ($this) {
            self::Query, self::PathParam, self::QueryPair => true,
            default => false,
        };
    }

    public function allowEmpty(): bool
    {
        return match ($this) {
            self::None, self::ReservedChars => false,
            default => true,
        };
    }

    /**
     * Removes percent encoding on reserved characters (used with + and # modifiers).
     */
    public function encode(string $var): string
    {
        return match ($this) {
            Operator::ReservedChars, Operator::Fragment => (string) Encoder::encodeQueryOrFragment($var),
            default => rawurlencode($var),
        };
    }

    /**
     * Decodes a URI template value.
     */
    public function decode(string $value): string
    {
        return match ($this) {
            Operator::ReservedChars, Operator::Fragment => (string) Encoder::decodeFragment($value),
            default => rawurldecode($value),
        };
    }

    /**
     * @throws SyntaxError if the expression is invalid
     * @throws SyntaxError if the operator used in the expression is invalid
     * @throws SyntaxError if the contained variable specifiers are invalid
     *
     * @return array{operator:Operator, variables:string}
     */
    public static function parseExpression(Stringable|string $expression): array
    {
        $expression = (string) $expression;
        if (1 !== preg_match(self::REGEXP_EXPRESSION, $expression, $parts)) {
            throw new SyntaxError('The expression "'.$expression.'" is invalid.');
        }

        $parts = $parts + ['operator' => ''];
        if ('' !== $parts['operator'] && str_contains(self::RESERVED_OPERATOR, $parts['operator'])) {
            throw new SyntaxError('The operator used in the expression "'.$expression.'" is reserved.');
        }

        return [
            'operator' => self::from($parts['operator']),
            'variables' => $parts['variables'],
        ];
    }

    /**
     * Replaces an expression with the given variables.
     *
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     * @throws TemplateCanNotBeExpanded if the variables contains nested array values
     */
    public function expand(VarSpecifier $varSpecifier, VariableBag $variables): string
    {
        $value = $variables->fetch($varSpecifier->name);
        if (null === $value) {
            return '';
        }

        [$expanded, $actualQuery] = $this->inject($value, $varSpecifier);
        if (!$actualQuery) {
            return $expanded;
        }

        if ('&' !== $this->separator() && '' === $expanded) {
            return $varSpecifier->name;
        }

        return $varSpecifier->name.'='.$expanded;
    }

    /**
     * @param string|array<string> $value
     *
     * @return array{0:string, 1:bool}
     */
    private function inject(array|string $value, VarSpecifier $varSpec): array
    {
        if (is_array($value)) {
            return $this->replaceList($value, $varSpec);
        }

        if (':' === $varSpec->modifier) {
            $value = mb_substr($value, 0, $varSpec->position, 'UTF-8');
        }

        return [$this->encode($value), $this->isNamed()];
    }

    /**
     * Expands an expression using a list of values.
     *
     * @param array<string> $value
     *
     * @throws TemplateCanNotBeExpanded if the variables is an array and a ":" modifier needs to be applied
     *
     * @return array{0:string, 1:bool}
     */
    private function replaceList(array $value, VarSpecifier $varSpec): array
    {
        if (':' === $varSpec->modifier) {
            throw TemplateCanNotBeExpanded::dueToUnableToProcessValueListWithPrefix($varSpec->name);
        }

        if ([] === $value) {
            return ['', false];
        }

        $pairs = [];
        $isList = array_is_list($value);
        $useQuery = $this->isNamed();
        foreach ($value as $key => $var) {
            if (!$isList) {
                $key = rawurlencode((string) $key);
            }

            $var = $this->encode($var);
            if ('*' === $varSpec->modifier) {
                if (!$isList) {
                    $var = $key.'='.$var;
                } elseif ($key > 0 && $useQuery) {
                    $var = $varSpec->name.'='.$var;
                }
            }

            $pairs[$key] = $var;
        }

        if ('*' === $varSpec->modifier) {
            if (!$isList) {
                // Don't prepend the value name when using the `explode` modifier with an associative array.
                $useQuery = false;
            }

            return [implode($this->separator(), $pairs), $useQuery];
        }

        if (!$isList) {
            // When an associative array is encountered and the `explode` modifier is not set, then
            // the result must be a comma separated list of keys followed by their respective values.
            $retVal = [];
            foreach ($pairs as $offset => $data) {
                $retVal[$offset] = $offset.','.$data;
            }
            $pairs = $retVal;
        }

        return [implode(',', $pairs), $useQuery];
    }

    /**
     * Extracts a variable from an operator value.
     *
     * A null value represents an absent variable. Exploded variables are delegated
     * to list extraction, while named non-exploded variables must contain the
     * expected variable name. Path and fragment operators may represent non-exploded
     * values as key/value pairs. Extracted values are percent-decoded before being
     * returned.
     *
     * @throws VariableCanNotBeExtracted If the value cannot be extracted according
     *                                   to the variable specifier.
     */
    public function extract(VarSpecifier $varSpecifier, string|null $value): ExtractionResult
    {
        if (null === $value) {
            return ExtractionResult::success([$varSpecifier->name => ExtractedValue::fromNull()]);
        }

        if ('*' === $varSpecifier->modifier) {
            return ExtractionResult::success([$varSpecifier->name => ExtractedValue::fromList($value, $varSpecifier, $this)]);
        }

        if ($this->isNamed()) {
            [$name, $value] = array_pad(explode('=', $value, 2), 2, '');
            if ($name !== $varSpecifier->name) {
                return ExtractionResult::empty();
            }
        }

        return ExtractionResult::success([$varSpecifier->name => ExtractedValue::fromValue($value, $varSpecifier, $this)]);
    }
}

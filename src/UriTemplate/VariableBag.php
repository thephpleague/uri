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

use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use Stringable;
use function is_bool;
use function is_object;
use function is_scalar;

final class VariableBag
{
    /**
     * @var array<string,string|array<string>>
     */
    private array $variables = [];

    /**
     * @param iterable<string,string|bool|int|float|array<string|bool|int|float>> $variables
     */
    public function __construct(iterable $variables = [])
    {
        foreach ($variables as $name => $value) {
            $this->assign($name, $value);
        }
    }

    public static function __set_state(array $properties): self
    {
        return new self($properties['variables']);
    }

    /**
     * @return array<string,string|array<string>>
     */
    public function all(): array
    {
        return $this->variables;
    }

    /**
     * Fetches the variable value if none found returns null.
     *
     * @return null|string|array<string>
     */
    public function fetch(string $name): null|string|array
    {
        return $this->variables[$name] ?? null;
    }

    /**
     * @param string|bool|int|float|null|array<string|bool|int|float> $value
     */
    public function assign(string $name, string|bool|int|float|array|null $value): void
    {
        $this->variables[$name] = $this->normalizeValue($value, $name, true);
    }

    /**
     * @param Stringable|string|float|int|bool|null $value the value to be expanded
     *
     * @throws TemplateCanNotBeExpanded if the value contains nested list
     *
     * @return string|array<string>
     */
    private function normalizeValue(Stringable|array|string|float|int|bool|null $value, string $name, bool $isNestedListAllowed): array|string
    {
        if (is_bool($value)) {
            return true === $value ? '1' : '0';
        }

        if (null === $value || is_scalar($value) || is_object($value)) {
            return (string) $value;
        }

        if (!$isNestedListAllowed) {
            throw TemplateCanNotBeExpanded::dueToNestedListOfValue($name);
        }

        foreach ($value as &$var) {
            $var = self::normalizeValue($var, $name, false);
        }
        unset($var);

        return $value;
    }

    /**
     * Replaces elements from passed variables into the current instance.
     */
    public function replace(VariableBag $variables): self
    {
        return new self($this->variables + $variables->variables);
    }
}

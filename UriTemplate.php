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

use League\Uri\Contracts\UriException;
use League\Uri\Contracts\UriInterface;
use League\Uri\Exceptions\SyntaxError;
use League\Uri\UriTemplate\Template;
use League\Uri\UriTemplate\TemplateCanNotBeExpanded;
use League\Uri\UriTemplate\VariableBag;
use Stringable;
use function array_fill_keys;
use function array_key_exists;

/**
 * Defines the URI Template syntax and the process for expanding a URI Template into a URI reference.
 *
 * @link    https://tools.ietf.org/html/rfc6570
 * @package League\Uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   6.1.0
 */
final class UriTemplate implements Stringable
{
    private readonly Template $template;
    private readonly VariableBag $defaultVariables;

    /**
     * @throws SyntaxError              if the template syntax is invalid
     * @throws TemplateCanNotBeExpanded if the template or the variables are invalid
     */
    public function __construct(Template|Stringable|string $template, iterable $defaultVariables = [])
    {
        $this->template = $template instanceof Template ? $template : Template::new($template);
        $this->defaultVariables = $this->filterVariables($defaultVariables);
    }

    private function filterVariables(iterable $variables): VariableBag
    {
        if (!$variables instanceof VariableBag) {
            $variables = new VariableBag($variables);
        }

        $offsets = array_fill_keys($this->template->variableNames, 1);

        return $variables
            ->filter(fn ($value, string|int $name) => array_key_exists($name, $offsets));
    }

    public function __toString(): string
    {
        return $this->template->value;
    }

    /**
     * @return array<string>
     */
    public function getVariableNames(): array
    {
        return $this->template->variableNames;
    }

    public function getDefaultVariables(): iterable
    {
        return $this->defaultVariables;
    }

    /**
     * Returns a new instance with the updated default variables.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified default variables.
     *
     * If present, variables whose name is not part of the current template
     * possible variable names are removed.
     *
     * @throws TemplateCanNotBeExpanded if the variables are invalid
     */
    public function withDefaultVariables(iterable $defaultVariables): self
    {
        $defaultVariables = $this->filterVariables($defaultVariables);
        if ($defaultVariables == $this->defaultVariables) {
            return $this;
        }

        return new self($this->template, $defaultVariables);
    }

    /**
     * @throws TemplateCanNotBeExpanded if the variables are invalid
     * @throws UriException             if the resulting expansion can not be converted to a UriInterface instance
     */
    public function expand(iterable $variables = []): string
    {
        return $this->template->expand(
            $this->filterVariables($variables)->replace($this->defaultVariables)
        );
    }

    /**
     * @throws TemplateCanNotBeExpanded if the variables are invalid or missing
     * @throws UriException             if the resulting expansion can not be converted to a UriInterface instance
     */
    public function expandOrFail(iterable $variables = []): string
    {
        return $this->template->expandOrFail(
            $this->filterVariables($variables)->replace($this->defaultVariables)
        );
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated Since version 7.0.0
     * @codeCoverageIgnore
     * @see UriTemplate::__toString
     *
     * Returns the template string
     */
    public function getTemplate(): string
    {
        return $this->__toString();
    }
}

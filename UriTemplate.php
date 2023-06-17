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
use League\Uri\Exceptions\TemplateCanNotBeExpanded;
use League\Uri\UriTemplate\Template;
use League\Uri\UriTemplate\VariableBag;
use Stringable;

/**
 * Defines the URI Template syntax and the process for expanding a URI Template into a URI reference.
 *
 * @link    https://tools.ietf.org/html/rfc6570
 * @package League\Uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   6.1.0
 *
 * Based on GuzzleHttp\UriTemplate class in Guzzle v6.5.
 * @link https://github.com/guzzle/guzzle/blob/6.5/src/UriTemplate.php
 */
final class UriTemplate
{
    public readonly Template $template;
    public readonly VariableBag $defaultVariables;

    /**
     * @throws SyntaxError              if the template syntax is invalid
     * @throws TemplateCanNotBeExpanded if the template variables are invalid
     */
    public function __construct(Template|Stringable|string $template, iterable $defaultVariables = [])
    {
        $this->template = $template instanceof Template ? $template : Template::fromString($template);
        $this->defaultVariables = VariableBag::fromTemplate($this->template, $defaultVariables);
    }

    /**
     * Returns a new instance with the updated default variables.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified default variables.
     *
     * If present, variables whose name is not part of the current template
     * possible variable names are removed.
     */
    public function withDefaultVariables(iterable $defaultVariables): self
    {
        $variables = VariableBag::fromTemplate($this->template, $defaultVariables);
        if ($variables == $this->defaultVariables) {
            return $this;
        }

        return new self($this->template, $defaultVariables);
    }

    /**
     * @throws TemplateCanNotBeExpanded if the variable contains nested array values
     * @throws UriException             if the resulting expansion can not be converted to a UriInterface instance
     */
    public function expand(iterable $variables = []): UriInterface
    {
        return Uri::fromString(
            $this->template->expand(
                VariableBag::fromTemplate($this->template, $variables)
                    ->replace($this->defaultVariables)
            )
        );
    }
}

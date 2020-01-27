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

namespace League\Uri\Contracts;

interface UriTemplateInterface
{
    /**
     * The template string.
     */
    public function getTemplate(): string;

    /**
     * Returns the names of the variables in the template, in order.
     *
     * @return string[]
     */
    public function getVariableNames(): array;

    /**
     * Returns the default values used to expand the template.
     *
     * The returned list only contains variables whose name is part of the current template.
     *
     * @return array<string,string|array>
     */
    public function getDefaultVariables(): array;

    /**
     * @param array<string,string|array> $variables
     *
     * @throws UriTemplateException if the expansion can not be done
     * @throws UriException         if the resulting expansion can not be converted into a valid UriInterface instance
     */
    public function expand(array $variables = []): UriInterface;

    /**
     * Returns a new instance with the updated template.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified template value.
     *
     * @param object|string $template a string or an object with the __toString method
     *
     * @throws UriTemplateException if the new template is invalid
     */
    public function withTemplate($template): self;

    /**
     * Returns a new instance with the updated default variables.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified default variables.
     *
     * If present, variables whose name is not part of the current template
     * possible variable names are removed.
     *
     * @param array<string,string|array> $defaultVariables
     */
    public function withDefaultVariables(array $defaultVariables): self;
}

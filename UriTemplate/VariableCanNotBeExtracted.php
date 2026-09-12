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

use Exception;
use League\Uri\Contracts\UriException;
use Throwable;

use function implode;

class VariableCanNotBeExtracted extends Exception implements UriException
{
    protected array $missingVariables = [];

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getMissingVariables(): array
    {
        return $this->missingVariables;
    }

    public static function dueToMissingVariables(string $input, Template $tempate, ExtractionResult $result): self
    {
        $exception = new self('The value "'.$input.'" does not provide all variables defined by the expression "'.$tempate->value.'"; Missing: "'.implode('", "', $result->missingVariables).'".');
        $exception->missingVariables = $result->missingVariables;

        return $exception;
    }
}

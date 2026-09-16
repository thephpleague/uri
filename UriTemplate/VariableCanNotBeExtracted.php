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
use TypeError;

use function array_values;
use function get_debug_type;
use function implode;

class VariableCanNotBeExtracted extends Exception implements UriException
{
    /** @var list<string> */
    protected array $missingNames = [];
    /** @var list<ExtractionErrorReason> */
    protected array $reasons = [];

    public function __construct(string $message, array $reasons = [], array $missingVariables = [])
    {
        parent::__construct($message);

        $r = [];
        foreach ($reasons as $reason) {
            $reason instanceof ExtractionErrorReason || throw new TypeError('An extraction error value must be an '.ExtractionErrorReason::class.'; '.get_debug_type($reason).' received.');
            $r[$reason->name] = $reason;
        }

        $m = [];
        foreach ($missingVariables as $missingVariable) {
            is_string($missingVariable) || throw new TypeError('Missing variable name must be a string; '.get_debug_type($missingVariable).' received.');
            $m[$missingVariable] = $missingVariable;
        }

        $this->reasons = array_values($r);
        $this->missingNames = array_values($m);
    }

    public static function dueToMissingVariables(string $input, Template $template, ExtractionResult $result): self
    {
        return new self(
            'The value "'.$input.'" does not provide all variables defined by the expression "'.$template->value.'"; Missing: "'.implode('", "', $result->missingNames()).'".',
            [ExtractionErrorReason::MissingVariables],
            $result->missingNames()
        );
    }

    /**
     * @return list<ExtractionErrorReason>
     */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    /**
     * @return list<string>
     */
    public function getMissingNames(): array
    {
        return $this->missingNames;
    }
}

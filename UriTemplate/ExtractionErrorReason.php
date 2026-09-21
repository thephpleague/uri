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

enum ExtractionErrorReason
{
    case PrefixMismatch;
    case LiteralMismatch;
    case MalformedValue;
    case PrefixLengthExceeded;
    case ReconciliationFailed;
    case UnmatchedContent;
    case UndeterminedDelimiter;
    case UnsupportedOperation;
    case MissingVariables;
    case StringMismatch;
    case TypeMismatch;
    case ListMismatch;
    case VariableMismatch;
}

<?php

namespace Classid\TemplateReplacement\Exceptions;

use Exception;
use Throwable;

class MissingRequiredParameterException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

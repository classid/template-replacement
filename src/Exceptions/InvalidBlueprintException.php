<?php

namespace Classid\TemplateReplacement\Exceptions;

use Exception;
use Throwable;

class InvalidBlueprintException extends Exception
{
    public function __construct(string $message = "You are instantiate class with invalid blueprint", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

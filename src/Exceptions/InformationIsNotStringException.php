<?php

namespace Classid\TemplateReplacement\Exceptions;

use Exception;
use Throwable;

class InformationIsNotStringException extends Exception
{
    public function __construct(string $message = "Data information is not string", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

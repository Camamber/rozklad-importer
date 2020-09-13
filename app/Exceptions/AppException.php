<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AppException extends Exception
{
    public $publicMessage = "Можливо просто немає інтернету.";

    public function __construct(string $message = "Unhandled App Exception", int $code = 500, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromException(Throwable $previous)
    {
        return new self("Unhandled App Exception", 500, $previous);
    }


    public function getPublicMessage()
    {
        return $this->publicMessage;
    }
}

<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticationExceptionWithErrors extends Exception
{
    public function __construct(
        string $message = 'Authentication failed',
        public string $details = 'Authentication failed',
        int $code = 401,
        public ?array $errors = null
    ) {
        parent::__construct($message, $code);
    }

    public function render(Request $request): Response
    {
        return response([
            'message' => $this->message,
            'errors' => $this->errors,
        ])->setStatusCode($this->code);
    }
}

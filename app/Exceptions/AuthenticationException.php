<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AuthenticationException extends Exception
{
    public function __construct(
        private string $errorMessage = 'Authentication failed',
        private string $details = 'Authentication failed',
        private int $statusCode = 401
    ) {
        parent::__construct($errorMessage, $statusCode);
    }

    public function render(Request $request): Response
    {
        return response([
            'message' => $this->errorMessage,
        ])->setStatusCode($this->statusCode);
    }

    public function report(): void
    {
        Log::debug("$this->details. $this->errorMessage.", [$this->statusCode]);
    }
}

<?php

namespace App\Exceptions;

use App\Traits\DebugPosition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AuthenticationException extends Exception
{
    use DebugPosition;

    private string $context;

    public function __construct(
        private string $errorMessage = 'Authentication failed',
        private string $details = 'Authentication failed',
        private int $statusCode = 401,
        private array $errors = []
    ) {
        parent::__construct($errorMessage, $statusCode);
        $this->context = $this->getDebugPosition();
    }

    public function render(Request $request): Response
    {
        if (count($this->errors) > 0)
        {
            return response([
                'message' => $this->errorMessage,
                'errors' => $this->errors,
            ])->setStatusCode($this->statusCode);
        }
        return response([
            'message' => $this->errorMessage,
        ])->setStatusCode($this->statusCode);
    }

    public function report(): void
    {
        Log::channel('debug')->debug("{$this->errorMessage}. {$this->details}.", [
            'status' => $this->statusCode,
            'location' => $this->context,
        ]);
    }
}

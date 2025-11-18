<?php

namespace App\Exceptions;

use App\Traits\DebugPosition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LogicalException extends Exception
{
    use DebugPosition;

    public string $context;

    public function __construct(
        public string $errorMessage = 'Logical exception',
        public string $details = 'Logical exception',
        public int $statusCode = 400,
        public array $errors = []
    ) {
        parent::__construct($errorMessage, $statusCode);
        $this->context = $this->getDebugPosition();
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function render(Request $request): Response
    {
        if (count($this->errors) > 0) {
            return response([
                'message' => $this->errorMessage,
                'details' => $this->details,
                'errors' => $this->errors,
            ])->setStatusCode($this->statusCode);
        }

        return response([
            'message' => $this->errorMessage,
            'details' => $this->details,
        ])->setStatusCode($this->statusCode);
    }

    public function report(): void
    {
        Log::channel('app_log')->warning("Exception: {$this->errorMessage}. {$this->details}.", [
            'status' => $this->statusCode,
            'location' => $this->context,
            'errors' => $this->errors ?? ['No error payload'],
        ]);
    }
}

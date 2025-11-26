<?php

namespace App\Exceptions;

use App\Traits\DebugPosition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class AuthenticationException extends Exception
{
    use DebugPosition;

    private string $context;

    public function __construct(
        private string $errorMessage = 'Authentication failed',
        private string $details = 'Authentication failed',
        private int $statusCode = 400,
        private array $errors = []
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
        if (App::environment(['production', 'staging'])) {
            if (count($this->errors) > 0) {
                return response([
                    'message' => $this->errorMessage,
                    'errors' => $this->errors,
                ])->setStatusCode($this->statusCode);
            }

            return response([
                'message' => $this->errorMessage,
            ])->setStatusCode($this->statusCode);
        } else {
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
    }

    public function report(): void
    {
        Log::channel('app_log')->warning("Exception(AuthenticationException): {$this->errorMessage}. {$this->details}.", [
            'status' => $this->statusCode,
            'location' => $this->context,
            'errors' => $this->errors ?? ['No error payload'],
        ]);
    }
}

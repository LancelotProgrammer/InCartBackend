<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmptySuccessfulResponseResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     */
    public function __construct(
        private ?string $message = 'Operation successful',
        private ?int $statusCode = 200
    ) {
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->statusCode);
    }
}

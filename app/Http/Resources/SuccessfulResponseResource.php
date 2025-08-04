<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuccessfulResponseResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     */
    public function __construct(
        mixed $resource = [],
        private ?string $message = 'Operation successful',
        private ?int $statusCode = 200
    ) {
        parent::__construct($resource);
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
            'data' => $this->resource,
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->statusCode);
    }
}

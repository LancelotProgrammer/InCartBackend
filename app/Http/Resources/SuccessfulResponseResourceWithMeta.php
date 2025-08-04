<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuccessfulResponseResourceWithMeta extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     */
    public function __construct(
        mixed $resource = [],
        private mixed $meta = [],
        private ?string $message = 'Operation successful',
        private ?int $statusCode = 200
    ) {
        parent::__construct($resource);
        $this->message = $message;
        $this->meta = $meta;
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
            'meta' => $this->meta,
        ];
    }
}

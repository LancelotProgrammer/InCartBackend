<?php

namespace App\Http\Resources\Metadata;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetadataResource extends JsonResource
{
    /**
     * Create a new resource instance.
     */
    public function __construct(
        private int $perPage,
        private int $currentPage,
        private int $hasMorePages,
    ) {}

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'previous_page' => $this->currentPage > 1 ? $this->currentPage - 1 : null,
            'next_page' => $this->hasMorePages ? $this->currentPage + 1 : null,
        ];
    }
}

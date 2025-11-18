<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

trait HasPublishAttribute
{
    protected function ensureHasPublishedAt(): void
    {
        if (! array_key_exists('published_at', $this->attributes)) {
            Log::channel('app_log')->emergency(sprintf(
                "Traits: The model %s does not have a 'published_at' attribute.",
                static::class
            ));
            throw new InvalidArgumentException(sprintf(
                "The model %s does not have a 'published_at' attribute.",
                static::class
            ));
        }
    }

    public function publish(): void
    {
        $this->ensureHasPublishedAt();
        $this->published_at = now();
        $this->save();
    }

    public function unpublish(): void
    {
        $this->ensureHasPublishedAt();
        $this->published_at = null;
        $this->save();
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at');
    }

    public function scopeUnpublished(Builder $query): void
    {
        $query->whereNull('published_at');
    }

    public function isPublished(): bool
    {
        return ! is_null($this->published_at);
    }
}

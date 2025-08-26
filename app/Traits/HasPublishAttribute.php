<?php

namespace App\Traits;

use InvalidArgumentException;

trait HasPublishAttribute
{
    protected function ensureHasPublishedAt(): void
    {
        if (! array_key_exists('published_at', $this->attributes)) {
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

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeUnpublished($query)
    {
        return $query->whereNull('published_at');
    }

    public function isPublished(): bool
    {
        return ! is_null($this->published_at);
    }
}

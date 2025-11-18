<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

trait HasIsDefaultAttribute
{
    protected function ensureHasIsDefault(): void
    {
        if (! array_key_exists('is_default', $this->attributes)) {
            Log::channel('app_log')->emergency(sprintf(
                "Traits: The model %s does not have a 'is_default' attribute.",
                static::class
            ));
            throw new InvalidArgumentException(sprintf(
                "The model %s does not have a 'is_default' attribute.",
                static::class
            ));
        }
    }

    public function markAsDefault(): void
    {
        $this->ensureHasIsDefault();
        $this->is_default = true;
        $this->save();
    }

    public function unmarkAsDefault(): void
    {
        $this->ensureHasIsDefault();
        $this->is_default = false;
        $this->save();
    }
}

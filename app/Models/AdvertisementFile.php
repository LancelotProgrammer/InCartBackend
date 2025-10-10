<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AdvertisementFile extends Pivot
{
    public $incrementing = true;

    protected $table = 'advertisement_file';

    protected $fillable = ['advertisement_id', 'file_id', 'order'];

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}

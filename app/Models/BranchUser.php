<?php

namespace App\Models;

use App\Traits\HasPublishAttribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BranchUser extends Pivot
{
    use HasPublishAttribute;

    public $incrementing = true;

    protected $table = 'branch_user';

    protected $fillable = [
        'branch_id',
        'user_id'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

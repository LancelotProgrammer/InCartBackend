<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

class ForceApproveOrder extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'reason',
        'types',
        'order_id',
        'user_id',
    ];

    protected $casts = [
        'types' => 'array',
    ];

    protected $auditInclude = [
        'types',
        'reason',
        'order_id',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditsLogs(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}

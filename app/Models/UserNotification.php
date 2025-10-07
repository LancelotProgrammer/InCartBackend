<?php

namespace App\Models;

use App\Enums\UserNotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body', 'type', 'config', 'mark_as_read', 'user_id', 'file_id'];

    protected $casts = [
        'type' => UserNotificationType::class,
        'config' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}

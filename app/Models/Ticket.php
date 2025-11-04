<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['user_id', 'question', 'reply', 'is_important', 'processed_at', 'processed_by', 'branch_id'];

    protected $casts = [
        'is_important' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static function trimTicketNotificationReply(string $reply, int $maxLength = 150): string
    {
        return strlen($reply) > $maxLength
            ? substr($reply, 0, $maxLength).'...'
            : $reply;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel',
        'title',
        'message',
        'meta',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

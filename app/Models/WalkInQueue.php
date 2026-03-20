<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalkInQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'guest_name',
        'phone',
        'reason',
        'status',
        'queued_at',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}

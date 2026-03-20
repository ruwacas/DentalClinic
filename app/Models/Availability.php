<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'dentist_id',
        'day_of_week',
        'start_time',
        'end_time',
        'max_clients_per_day',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_clients_per_day' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dentist_id');
    }
}

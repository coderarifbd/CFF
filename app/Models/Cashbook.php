<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cashbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'type',
        'category',
        'amount',
        'reference_type',
        'reference_id',
        'note',
        'added_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

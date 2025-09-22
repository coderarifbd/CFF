<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'amount',
        'date',
        'agreement_document',
        'notes',
        'status',
        'return_date',
        'return_amount',
        'added_by',
    ];

    protected $casts = [
        'date' => 'date',
        'return_date' => 'date',
        'amount' => 'decimal:2',
        'return_amount' => 'decimal:2',
    ];

    public function interests(): HasMany
    {
        return $this->hasMany(InvestmentInterest::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function getTotalInterestAttribute(): string
    {
        return (string) $this->interests()->sum('amount');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = true; // uses created_at

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log($model, string $action = 'updated', array $changes = []): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'model_type' => is_string($model) ? $model : get_class($model),
            'model_id' => is_object($model) && method_exists($model, 'getKey') ? $model->getKey() : (int) ($model['id'] ?? 0),
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}

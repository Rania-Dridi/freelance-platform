<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'En attente',
        'accepted' => 'Acceptée',
        'rejected' => 'Refusée',
    ];

    protected $fillable = ['project_id', 'freelancer_id', 'message', 'price', 'status'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}

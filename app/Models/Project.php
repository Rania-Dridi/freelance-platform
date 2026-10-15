<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    public const CATEGORIES = [
        'web' => 'Développement web',
        'mobile' => 'Application mobile',
        'design' => 'Design et UX',
        'data' => 'Data et BI',
        'marketing' => 'Marketing digital',
        'redaction' => 'Rédaction et traduction',
    ];

    public const STATUS_LABELS = [
        'open' => 'Ouvert',
        'in_progress' => 'En cours',
        'completed' => 'Terminé',
    ];

    protected $fillable = ['client_id', 'title', 'description', 'category', 'budget', 'deadline', 'status'];

    protected function casts(): array
    {
        return ['deadline' => 'date'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}

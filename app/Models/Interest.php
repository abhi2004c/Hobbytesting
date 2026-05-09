<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Interest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'category', 'icon'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_interests')->withTimestamps();
    }

    public function scopeByCategory($q, string $category)
    {
        return $q->where('category', $category);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'color', 'description', 'is_active', 'order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'category_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('order');
    }
}
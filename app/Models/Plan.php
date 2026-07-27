<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'plan_slug', 'slug');
    }

    public function hasLimit(string $feature): bool
    {
        $value = $this->{$feature};

        return $value !== -1;
    }

    public function getLimit(string $feature): int
    {
        return $this->{$feature} ?? 0;
    }
}

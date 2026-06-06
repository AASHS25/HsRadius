<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['name', 'price', 'interval', 'max_customers', 'max_nas', 'is_active'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'max_customers' => 'integer',
            'max_nas' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}

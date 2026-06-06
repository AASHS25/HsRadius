<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'domain', 'status', 'plan_id', 'balance',
        'trial_ends_at', 'subscription_ends_at', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'settings' => 'array',
            'balance' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenantInvoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class);
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(TenantBalanceTransaction::class)->latest();
    }

    public function adjustBalance(float $amount, string $type, ?string $description = null): void
    {
        DB::transaction(function () use ($amount, $type, $description) {
            $this->balance = (float) $this->balance + $amount;
            $this->save();

            $this->balanceTransactions()->create([
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'balance_after' => $this->balance,
            ]);
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            || ($this->status === 'trial' && (! $this->trial_ends_at || $this->trial_ends_at->isFuture()));
    }
}

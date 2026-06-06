<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'username', 'password', 'fullname', 'email', 'phone', 'address',
        'service_type', 'package_id', 'nas_id', 'status', 'start_date',
        'expiry_date', 'static_ip', 'mac_address', 'notes', 'pppoe_caller_id',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'expiry_date' => 'datetime',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function nas(): BelongsTo
    {
        return $this->belongsTo(Nas::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isOnline(): bool
    {
        return RadAcct::where('username', $this->username)->whereNull('acctstoptime')->exists();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->expiry_date) return null;
        return max(0, (int) now()->diffInDays($this->expiry_date, false));
    }
}

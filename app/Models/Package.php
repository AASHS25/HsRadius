<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name', 'type', 'rate_up', 'rate_down', 'burst_up', 'burst_down',
        'burst_threshold_up', 'burst_threshold_down', 'burst_time_up', 'burst_time_down',
        'priority', 'validity_type', 'validity_value', 'validity_unit',
        'limit_type', 'quota_bytes', 'price', 'shared_users',
        'pool_name', 'dns_servers', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function getFormattedRateAttribute(): string
    {
        return $this->formatSpeed($this->rate_up) . '/' . $this->formatSpeed($this->rate_down);
    }

    public function getMikrotikRateAttribute(): string
    {
        $rate = "{$this->rate_up}k/{$this->rate_down}k";
        if ($this->burst_up && $this->burst_down) {
            $rate .= " {$this->burst_up}k/{$this->burst_down}k";
            $rate .= " {$this->burst_threshold_up}k/{$this->burst_threshold_down}k";
            $rate .= " {$this->burst_time_up}/{$this->burst_time_down}";
        }
        return $rate;
    }

    public function getRadiusGroupName(): string
    {
        return 'pkg-' . $this->id;
    }

    private function formatSpeed(int $kbps): string
    {
        if ($kbps >= 1024) return round($kbps / 1024, 1) . ' Mbps';
        return $kbps . ' Kbps';
    }
}

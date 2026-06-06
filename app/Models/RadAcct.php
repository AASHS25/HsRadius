<?php

namespace App\Models;

use App\Tenancy\BelongsToTenantViaUsername;
use Illuminate\Database\Eloquent\Model;

class RadAcct extends Model
{
    use BelongsToTenantViaUsername;

    protected $table = 'radacct';
    protected $primaryKey = 'radacctid';
    public $timestamps = false;

    protected $fillable = [
        'acctsessionid', 'acctuniqueid', 'username', 'realm',
        'nasipaddress', 'nasportid', 'nasporttype',
        'acctstarttime', 'acctupdatetime', 'acctstoptime', 'acctsessiontime',
        'acctauthentic', 'connectinfo_start', 'connectinfo_stop',
        'acctinputoctets', 'acctoutputoctets',
        'calledstationid', 'callingstationid', 'acctterminatecause',
        'servicetype', 'framedprotocol', 'framedipaddress',
    ];

    protected function casts(): array
    {
        return [
            'acctstarttime' => 'datetime',
            'acctupdatetime' => 'datetime',
            'acctstoptime' => 'datetime',
        ];
    }

    public function scopeOnline($query)
    {
        return $query->whereNull('acctstoptime');
    }

    public function scopeOffline($query)
    {
        return $query->whereNotNull('acctstoptime');
    }

    public function getFormattedUploadAttribute(): string
    {
        return $this->formatBytes($this->acctinputoctets ?? 0);
    }

    public function getFormattedDownloadAttribute(): string
    {
        return $this->formatBytes($this->acctoutputoctets ?? 0);
    }

    public function getFormattedSessionTimeAttribute(): string
    {
        $seconds = $this->acctsessiontime ?? 0;
        if ($seconds === 0 && $this->acctstarttime) {
            $seconds = now()->diffInSeconds($this->acctstarttime);
        }
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($days > 0) return "{$days}d {$hours}h {$minutes}m";
        return "{$hours}h {$minutes}m {$secs}s";
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $value = (float) $bytes;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }
        return round($value, 2) . ' ' . $units[$i];
    }
}

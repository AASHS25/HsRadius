<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nas extends Model
{
    use BelongsToTenant;

    protected $table = 'nas';

    protected $fillable = [
        'tenant_id', 'nasname', 'shortname', 'type', 'ports', 'secret', 'server',
        'community', 'description', 'api_host', 'api_port', 'api_username',
        'api_password', 'api_ssl', 'is_active',
    ];

    protected $hidden = ['secret', 'api_password'];

    protected function casts(): array
    {
        return [
            'api_ssl' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'nas_id');
    }

    public function getOnlineUsersCountAttribute(): int
    {
        return RadAcct::where('nasipaddress', $this->nasname)
            ->whereNull('acctstoptime')
            ->count();
    }
}

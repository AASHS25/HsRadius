<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-tenant key/value settings. Accessed by explicit tenant_id (not the
 * tenant global scope) because some flows (payment webhook, portal) run
 * without a resolved CurrentTenant.
 */
class Setting extends Model
{
    protected $fillable = ['tenant_id', 'key', 'value'];

    public static function get(?int $tenantId, string $key, $default = null)
    {
        return static::where('tenant_id', $tenantId)->where('key', $key)->value('value') ?? $default;
    }

    public static function put(?int $tenantId, string $key, $value): void
    {
        static::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => $value],
        );
    }
}

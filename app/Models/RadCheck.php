<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RadCheck extends Model
{
    use BelongsToTenant;

    protected $table = 'radcheck';
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'username', 'attribute', 'op', 'value'];
}

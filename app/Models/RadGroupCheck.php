<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RadGroupCheck extends Model
{
    use BelongsToTenant;

    protected $table = 'radgroupcheck';
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'groupname', 'attribute', 'op', 'value'];
}

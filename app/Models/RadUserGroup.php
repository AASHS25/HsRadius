<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RadUserGroup extends Model
{
    use BelongsToTenant;

    protected $table = 'radusergroup';
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'username', 'groupname', 'priority'];
}

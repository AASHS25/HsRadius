<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RadGroupReply extends Model
{
    use BelongsToTenant;

    protected $table = 'radgroupreply';
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'groupname', 'attribute', 'op', 'value'];
}

<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RadReply extends Model
{
    use BelongsToTenant;

    protected $table = 'radreply';
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'username', 'attribute', 'op', 'value'];
}

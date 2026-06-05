<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadPostAuth extends Model
{
    protected $table = 'radpostauth';
    public $timestamps = false;

    protected $fillable = ['username', 'pass', 'reply', 'calledstationid', 'callingstationid', 'authdate'];

    protected function casts(): array
    {
        return ['authdate' => 'datetime'];
    }
}

<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'system';
    protected $guarded = [];
    protected $casts = [
        'configs' => 'json',
        'setup_has_done' => 'boolean'
    ];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}

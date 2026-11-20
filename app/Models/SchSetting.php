<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchSetting extends Model
{
    public $timestamps = false;

    protected $table = 'sch_settings';

    protected $guarded = [];

    public static function primary(): ?self
    {
        return static::query()->first();
    }
}
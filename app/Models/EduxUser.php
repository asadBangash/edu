<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduxUser extends Model
{
    public $timestamps = false;

    protected $table = 'users';

    protected $guarded = [];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    public $timestamps = false;

    protected $table = 'staff';

    protected $guarded = [];

    public function staffRoles(): HasMany
    {
        return $this->hasMany(StaffRole::class, 'staff_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Izinkan mass assignment untuk semua field
    protected $guarded = [];

    /**
     * Relasi ke Users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}

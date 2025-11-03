<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
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

    /**
     * Relasi ke Form Transaksi
     */
    public function transaksiForms()
    {
        return $this->hasMany(TransaksiForm::class);
    }
}

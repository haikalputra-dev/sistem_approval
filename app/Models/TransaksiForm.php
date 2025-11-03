<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiForm extends Model
{
    use HasFactory;

    // Izinkan mass assignment untuk semua field
    protected $guarded = [];

        protected $casts = [
        'tanggal_pengajuan' => 'datetime', // <-- INI ADALAH PERBAIKANNYA
        'rencana_tanggal_transaksi' => 'date', // <-- Sebaiknya kita cast ini juga
        'total_nominal' => 'decimal:2',     // <-- Ini juga best practice
    ];

    /**
     * Relasi ke User (Pemohon)
     */
    public function pemohon()
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    /**
     * Relasi ke Perusahaan
     */
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    /**
     * Relasi ke Detail Transaksi
     */
    public function details()
    {
        return $this->hasMany(TransaksiDetail::class);
    }

    /**
     * Relasi ke Attachment
     */
    public function attachments()
    {
        return $this->hasMany(TransaksiAttachment::class);
    }

    /**
     * Relasi ke History
     */
    public function history()
    {
        return $this->hasMany(TransaksiHistory::class);
    }


}

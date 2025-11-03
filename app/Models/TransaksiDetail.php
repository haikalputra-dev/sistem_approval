<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    use HasFactory;

    // Tentukan kolom yang boleh diisi
    protected $fillable = [
        'transaksi_form_id',
        'pengakuan_transaksi',
        'nominal',
        'keterangan_detail',
    ];

    /**
     * Definisikan relasi: Satu Detail milik satu Form.
     */
    public function form()
    {
        return $this->belongsTo(TransaksiForm::class, 'transaksi_form_id');
    }
}

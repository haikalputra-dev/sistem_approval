<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiHistory extends Model
{
    use HasFactory;

    protected $table = 'transaksi_history';

    protected $fillable = [
        'transaksi_form_id',
        'user_id',
        'action', // Cth: 'Approve', 'Reject', 'Submit'
        'remarks', // Catatan (penting untuk reject)
        'from_status',
        'to_status',
    ];

    /**
     * Relasi ke User (Siapa yang melakukan aksi).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Form Transaksi.
     */
    public function form()
    {
        return $this->belongsTo(TransaksiForm::class, 'transaksi_form_id');
    }
}

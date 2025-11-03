<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiAttachment extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak jamak (sama seperti TransaksiHistory)
    protected $table = 'transaksi_attachments';

    protected $fillable = [
        'transaksi_form_id',
        'file_name', // Nama file asli
        'file_path', // Path di storage
        'attachment_type', // (Opsional, misal: 'Invoice', 'PO')
        'uploaded_by',
    ];

    /**
     * Relasi ke Form Induk.
     */
    public function form()
    {
        return $this->belongsTo(TransaksiForm::class, 'transaksi_form_id');
    }

    /**
     * Relasi ke User yang upload.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

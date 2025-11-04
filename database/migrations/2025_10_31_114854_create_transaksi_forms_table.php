<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pemohon_id');
            $table->unsignedBigInteger('perusahaan_id');
            $table->dateTime('tanggal_pengajuan');

            // Kolom Baru (Revisi)
            $table->string('kategori_uraian')->nullable();

            $table->text('uraian_transaksi');

            // Kolom Baru (Revisi)
            $table->string('kategori_pengakuan')->nullable();

            $table->decimal('total_nominal', 15, 2);

            // Kolom 'dasar_transaksi' (lama) DIHAPUS dan DIGANTI dengan 2 kolom ini:
            $table->string('tipe_dasar_transaksi')->nullable(); // Cth: 'nota', 'invoice', 'pernyataan_direksi'
            $table->text('keterangan_dasar_transaksi')->nullable(); // Untuk isi keterangan pernyataan direksi

            $table->string('lawan_transaksi')->nullable();
            $table->string('rekening_transaksi')->nullable();
            $table->date('rencana_tanggal_transaksi')->nullable();
            $table->text('keterangan_form')->nullable();
            $table->string('status', 50)->default('Draft'); // Cth: Draft, Diajukan, Disetujui Direksi, ...

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_forms');
    }
};


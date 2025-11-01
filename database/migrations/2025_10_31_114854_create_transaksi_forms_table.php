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
            $table->date('tanggal_pengajuan');
            $table->text('uraian_transaksi');
            $table->decimal('total_nominal', 15, 2);
            $table->string('dasar_transaksi')->nullable();
            $table->string('lawan_transaksi')->nullable();
            $table->string('rekening_transaksi')->nullable();
            $table->date('rencana_tanggal_transaksi');
            $table->text('keterangan_form')->nullable();
            $table->string('status', 50)->default('Draft'); // Misal: Draft, Submitted, Approved_PYB1, Approved_PYB2, Approved_Direksi, Rejected
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

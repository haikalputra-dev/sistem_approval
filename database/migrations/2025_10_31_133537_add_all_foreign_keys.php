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
        // Foreign keys untuk tabel 'users'
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
            $table->foreign('perusahaan_id')->references('id')->on('perusahaans')->onDelete('set null');
        });

        // Foreign keys untuk tabel 'transaksi_forms'
        Schema::table('transaksi_forms', function (Blueprint $table) {
            $table->foreign('pemohon_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('perusahaan_id')->references('id')->on('perusahaans')->onDelete('restrict');
        });

        // Foreign keys untuk tabel 'transaksi_attachments'
        Schema::table('transaksi_attachments', function (Blueprint $table) {
            $table->foreign('transaksi_form_id')->references('id')->on('transaksi_forms')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('restrict');
        });

        // Foreign keys untuk tabel 'transaksi_history'
        Schema::table('transaksi_history', function (Blueprint $table) {
            $table->foreign('transaksi_form_id')->references('id')->on('transaksi_forms')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['perusahaan_id']);
        });

        Schema::table('transaksi_forms', function (Blueprint $table) {
            $table->dropForeign(['pemohon_id']);
            $table->dropForeign(['perusahaan_id']);
        });

        Schema::table('transaksi_attachments', function (Blueprint $table) {
            $table->dropForeign(['transaksi_form_id']);
            $table->dropForeign(['uploaded_by']);
        });

        Schema::table('transaksi_history', function (Blueprint $table) {
            $table->dropForeign(['transaksi_form_id']);
            $table->dropForeign(['user_id']);
        });
    }
};

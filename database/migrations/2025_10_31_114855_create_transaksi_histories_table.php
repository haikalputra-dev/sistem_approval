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
        Schema::create('transaksi_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_form_id');
            $table->unsignedBigInteger('user_id'); // User yang melakukan aksi
            $table->string('action'); // Misal: Submitted, Approved, Rejected, Commented
            $table->text('remarks')->nullable(); // Komentar/catatan
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_history');
    }
};

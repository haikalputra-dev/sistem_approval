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
        Schema::create('transaksi_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_form_id');
            $table->string('file_name'); // Nama file asli
            $table->string('file_path'); // Path di storage
            $table->string('attachment_type')->nullable(); // Misal: Invoice, PO, dll
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_attachments');
    }
};

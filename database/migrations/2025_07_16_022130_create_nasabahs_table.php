<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nasabahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nama_umplung');
            $table->string('nomor_rekening', 20)->unique();
            $table->string('alamat');
            $table->string('nomor_hp');
            $table->string('qr_token', 64)->unique(); // ✅ Token QR permanen
            $table->foreignId('blok_pasar_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nasabahs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('penjadwalan_harians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('blok_id')->constrained('blok_pasars')->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ditetapkan_oleh')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjadwalan_harians');
    }
};

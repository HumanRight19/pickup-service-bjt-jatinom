<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('setoran_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('setoranable'); // Bisa ke Setoran atau TitipSetoran
            $table->enum('type', ['update', 'batal']);
            $table->integer('jumlah_lama')->nullable();
            $table->integer('jumlah_baru')->nullable();
            $table->text('alasan')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran_requests');
    }
};

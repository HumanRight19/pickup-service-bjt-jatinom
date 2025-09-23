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
        Schema::create('setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Petugas
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->integer('jumlah')->nullable(); // ✅ pakai nullable, bukan default(0)
            $table->enum('status', ['sudah_setor', 'batal'])->default('sudah_setor'); // ✅ kolom status
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setorans');
    }
};

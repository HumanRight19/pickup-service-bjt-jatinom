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
        Schema::table('nasabahs', function (Blueprint $table) {
            if (!Schema::hasColumn('nasabahs', 'alamat')) {
                $table->string('alamat')->nullable();
            }
            if (!Schema::hasColumn('nasabahs', 'no_hp')) {
                $table->string('no_hp')->nullable();
            }
            if (!Schema::hasColumn('nasabahs', 'blok_pasar_id')) {
                $table->foreignId('blok_pasar_id')->nullable()->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('nasabahs', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'no_hp']);
            $table->dropForeign(['blok_pasar_id']);
            $table->dropColumn('blok_pasar_id');
        });
    }

};

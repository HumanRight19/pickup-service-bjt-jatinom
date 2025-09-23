<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('nasabahs', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id')->nullable();
        });

        // Generate UUID untuk nasabah yang sudah ada
        \App\Models\Nasabah::whereNull('uuid')->each(function ($nasabah) {
            $nasabah->uuid = Str::uuid();
            $nasabah->save();
        });
    }

    public function down()
    {
        Schema::table('nasabahs', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};

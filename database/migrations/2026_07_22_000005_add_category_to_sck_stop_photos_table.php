<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sck_stop_photos', function (Blueprint $table) {
            $table->string('category', 30)->default('documentation')->after('caption');
        });
    }

    public function down(): void
    {
        Schema::table('sck_stop_photos', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};

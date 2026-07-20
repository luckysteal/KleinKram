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
        Schema::table('sck_warehouse_items', function (Blueprint $table) {
            $table->boolean('datev_exported')->default(false)->after('kommentar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sck_warehouse_items', function (Blueprint $table) {
            $table->dropColumn('datev_exported');
        });
    }
};

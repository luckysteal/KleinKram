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
            $table->string('artikelgruppe')->nullable()->after('geraet');
            $table->string('einheit')->default('Stück')->after('artikelgruppe');
            $table->string('steuersatz')->default('19')->after('einheit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sck_warehouse_items', function (Blueprint $table) {
            $table->dropColumn(['artikelgruppe', 'einheit', 'steuersatz']);
        });
    }
};

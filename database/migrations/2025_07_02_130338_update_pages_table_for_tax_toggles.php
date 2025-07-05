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
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('global_tax_enabled')->default(false)->after('content');
            $table->renameColumn('tax_enabled', 'german_tax_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('global_tax_enabled');
            $table->renameColumn('german_tax_enabled', 'tax_enabled');
        });
    }
};

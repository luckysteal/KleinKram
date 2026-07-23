<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sck_customers', function (Blueprint $table) {
            $table->string('datev_account', 9)->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('sck_customers', function (Blueprint $table) {
            $table->dropUnique(['datev_account']);
            $table->dropColumn('datev_account');
        });
    }
};

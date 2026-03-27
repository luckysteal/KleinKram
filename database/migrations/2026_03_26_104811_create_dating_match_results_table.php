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
        Schema::create('dating_match_results', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('seeking')->nullable();
            $table->string('franchise')->nullable();
            $table->string('mapped_character');
            $table->json('traits');
            $table->json('partner_traits');
            $table->json('full_results'); // Flexible question-answer pairs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dating_match_results');
    }
};

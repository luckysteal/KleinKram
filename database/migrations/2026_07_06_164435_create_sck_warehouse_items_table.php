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
        Schema::create('sck_warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->string('bezeichnung');
            $table->string('geraet');
            $table->string('lieferant');
            $table->decimal('ek_ohne_st', 10, 2);
            $table->decimal('vk_ohne_st', 10, 2);
            $table->string('alte_artikelnummer')->nullable();
            $table->string('neue_artikelnummer')->unique();
            $table->integer('stueckzahl')->default(0);
            $table->text('kommentar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sck_warehouse_items');
    }
};

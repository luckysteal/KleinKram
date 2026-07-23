<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sck_map_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('note')->nullable();
            $table->string('formatted_address')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number', 40)->nullable();
            $table->string('postal_code', 20)->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('country_code', 2)->default('DE');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sck_map_points');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sck_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('street')->nullable();
            $table->string('house_number', 40)->nullable();
            $table->string('postal_code', 20)->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('country_code', 2)->default('DE');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('reputation_rating')->nullable();
            $table->text('reputation_note')->nullable();
            $table->date('reputation_reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sck_customer_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('sck_customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 30);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('sck_route_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('home_name')->default('Home');
            $table->string('home_address')->nullable();
            $table->decimal('home_latitude', 10, 7)->nullable();
            $table->decimal('home_longitude', 10, 7)->nullable();
            $table->decimal('travel_base_fee', 10, 2)->default(0);
            $table->decimal('travel_per_km', 10, 2)->default(0.70);
            $table->decimal('travel_per_minute', 10, 2)->default(0);
            $table->decimal('travel_minimum_fee', 10, 2)->default(0);
            $table->decimal('internal_per_km', 10, 2)->default(0.35);
            $table->decimal('internal_per_minute', 10, 2)->default(0);
            $table->string('datev_consultant_number')->default('1001');
            $table->string('datev_client_number')->default('1');
            $table->string('datev_chart', 2)->default('03');
            $table->string('datev_revenue_19')->default('8400');
            $table->string('datev_revenue_7')->default('8300');
            $table->string('datev_debtor_account')->default('1400');
            $table->boolean('datev_verified')->default(false);
            $table->timestamp('datev_reminder_snoozed_until')->nullable();
            $table->timestamps();
        });

        Schema::create('sck_stop_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('sck_customers')->nullOnDelete();
            $table->string('title');
            $table->string('street')->nullable();
            $table->string('house_number', 40)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country_code', 2)->default('DE');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('service_minutes')->default(30);
            $table->time('window_start')->nullable();
            $table->time('window_end')->nullable();
            $table->unsignedTinyInteger('priority')->default(3);
            $table->string('recurrence')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('access_notes')->nullable();
            $table->text('parking_notes')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sck_stop_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stop_template_id')->constrained('sck_stop_templates')->cascadeOnDelete();
            $table->foreignId('warehouse_item_id')->constrained('sck_warehouse_items')->cascadeOnDelete();
            $table->decimal('suggested_quantity', 10, 2)->default(1);
            // MySQL limits identifiers to 64 characters. Laravel's generated
            // name would be 65 characters for this compound index.
            $table->unique(['stop_template_id', 'warehouse_item_id'], 'sck_stop_template_item_unique');
        });

        Schema::create('sck_weekly_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('week_start')->index();
            $table->string('status')->default('draft')->index();
            $table->unsignedTinyInteger('tour_count')->default(5);
            $table->json('parameters')->nullable();
            $table->unsignedBigInteger('selected_candidate_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sck_weekly_plan_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_plan_id')->constrained('sck_weekly_plans')->cascadeOnDelete();
            $table->foreignId('stop_template_id')->nullable()->constrained('sck_stop_templates')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('sck_customers')->nullOnDelete();
            $table->string('title');
            $table->string('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('service_minutes')->default(30);
            $table->json('allowed_weekdays')->nullable();
            $table->date('required_date')->nullable();
            $table->time('window_start')->nullable();
            $table->time('window_end')->nullable();
            $table->unsignedTinyInteger('priority')->default(3);
            $table->unsignedTinyInteger('fixed_tour_index')->nullable();
            $table->unsignedSmallInteger('fixed_position')->nullable();
            $table->string('direction', 3)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sck_plan_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_plan_id')->constrained('sck_weekly_plans')->cascadeOnDelete();
            $table->string('strategy', 30);
            $table->string('name');
            $table->decimal('score', 14, 4)->default(0);
            $table->boolean('feasible')->default(true);
            $table->json('metrics')->nullable();
            $table->json('tours');
            $table->json('unassigned')->nullable();
            $table->timestamps();
            $table->unique(['weekly_plan_id', 'strategy']);
        });

        Schema::create('sck_tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('weekly_plan_id')->nullable()->constrained('sck_weekly_plans')->nullOnDelete();
            $table->string('number')->unique();
            $table->string('title');
            $table->date('tour_date')->nullable()->index();
            $table->time('departure_time')->nullable();
            $table->string('status')->default('draft')->index();
            $table->json('start_snapshot');
            $table->json('end_snapshot');
            $table->string('route_provider')->nullable();
            $table->boolean('route_optimized')->default(false);
            $table->mediumText('encoded_polyline')->nullable();
            $table->decimal('planned_km', 10, 2)->default(0);
            $table->unsignedInteger('planned_drive_minutes')->default(0);
            $table->unsignedInteger('planned_service_minutes')->default(0);
            $table->decimal('travel_fee_pool', 10, 2)->default(0);
            $table->decimal('internal_travel_cost', 10, 2)->default(0);
            $table->json('pricing_snapshot')->nullable();
            $table->json('route_warnings')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sck_tour_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('sck_tours')->cascadeOnDelete();
            $table->foreignId('stop_template_id')->nullable()->constrained('sck_stop_templates')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('sck_customers')->nullOnDelete();
            $table->unsignedInteger('position');
            $table->string('type', 20)->default('service');
            $table->string('title');
            $table->json('address_snapshot')->nullable();
            $table->json('customer_snapshot')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('service_minutes')->default(30);
            $table->time('window_start')->nullable();
            $table->time('window_end')->nullable();
            $table->unsignedTinyInteger('priority')->default(3);
            $table->boolean('position_locked')->default(false);
            $table->unsignedInteger('arrival_minutes')->nullable();
            $table->decimal('cumulative_km', 10, 2)->nullable();
            $table->decimal('allocated_travel_fee', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tour_id', 'position']);
        });

        Schema::create('sck_processed_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_stop_id')->constrained('sck_tour_stops')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_hash', 64)->unique();
            $table->string('file_name');
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->timestamps();
        });

        Schema::create('sck_tour_stop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_stop_id')->constrained('sck_tour_stops')->cascadeOnDelete();
            $table->foreignId('warehouse_item_id')->nullable()->constrained('sck_warehouse_items')->nullOnDelete();
            $table->foreignId('processed_invoice_id')->nullable()->constrained('sck_processed_invoices')->nullOnDelete();
            $table->string('item_name');
            $table->string('article_number')->nullable();
            $table->string('unit')->default('Stück');
            $table->decimal('quantity', 10, 2);
            $table->decimal('ek_snapshot', 10, 2)->default(0);
            $table->decimal('vk_snapshot', 10, 2)->default(0);
            $table->decimal('actual_net_price', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(19);
            $table->string('source', 20)->default('manual');
            $table->decimal('stock_deducted', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sck_stop_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_stop_id')->constrained('sck_tour_stops')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('sck_customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sck_comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sck_weekly_plans', function (Blueprint $table) {
            $table->foreign('selected_candidate_id')->references('id')->on('sck_plan_candidates')->nullOnDelete();
        });

        Schema::table('sck_warehouse_logs', function (Blueprint $table) {
            $table->foreignId('tour_id')->nullable()->after('item_id')->constrained('sck_tours')->nullOnDelete();
            $table->foreignId('tour_stop_id')->nullable()->after('tour_id')->constrained('sck_tour_stops')->nullOnDelete();
            $table->decimal('quantity', 10, 2)->nullable()->after('tour_stop_id');
            $table->string('invoice_hash', 64)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('sck_warehouse_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tour_stop_id');
            $table->dropConstrainedForeignId('tour_id');
            $table->dropColumn(['quantity', 'invoice_hash']);
        });
        Schema::table('sck_weekly_plans', fn (Blueprint $table) => $table->dropForeign(['selected_candidate_id']));
        foreach (['sck_comments', 'sck_stop_photos', 'sck_tour_stop_items', 'sck_processed_invoices', 'sck_tour_stops', 'sck_tours', 'sck_plan_candidates', 'sck_weekly_plan_stops', 'sck_weekly_plans', 'sck_stop_template_items', 'sck_stop_templates', 'sck_route_settings', 'sck_customer_changes', 'sck_customers'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

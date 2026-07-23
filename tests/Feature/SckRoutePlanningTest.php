<?php

namespace Tests\Feature;

use App\Models\Sck\SckCustomer;
use App\Models\Sck\SckMapPoint;
use App\Models\Sck\SckRouteSetting;
use App\Models\Sck\SckStopTemplate;
use App\Models\Sck\SckStopPhoto;
use App\Models\Sck\SckTour;
use App\Models\Sck\SckWarehouseItem;
use App\Models\Sck\SckWeeklyPlan;
use App\Models\User;
use App\Services\Sck\RouteGeometryCodec;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SckRoutePlanningTest extends TestCase
{
    use RefreshDatabase;

    public function test_sck_subapps_require_sck_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('sck.kunden.index'))->assertForbidden();
        $this->actingAs($user)->get(route('sck.wochenplanung.index'))->assertForbidden();
        $this->actingAs($user)->get(route('sck.map.index'))->assertForbidden();
    }

    public function test_a_stop_can_be_added_from_an_existing_tour(): void
    {
        config(['services.tomtom.key' => 'test-key']);
        Http::fake([
            'https://api.tomtom.com/search/2/search/*' => Http::response(['results' => [[
                'address' => ['freeformAddress' => 'Markt 1, 53111 Bonn'],
                'position' => ['lat' => 50.735, 'lon' => 7.102],
            ]]]),
            'https://api.tomtom.com/routing/1/calculateRoute/*' => Http::response(['routes' => [[
                'summary' => ['lengthInMeters' => 15000, 'travelTimeInSeconds' => 1800],
                'legs' => [],
            ]]]),
        ]);
        $user = $this->sckUser();
        [$tour] = $this->tourAndStop($user);

        $this->actingAs($user)->post(route('sck.routen.stopps.store', $tour), [
            'show_stop_address' => 'Markt 1, 53111 Bonn', 'type' => 'service', 'service_minutes' => 45, 'priority' => 3,
        ])->assertRedirect(route('sck.routen.show', $tour));

        $this->assertDatabaseHas('sck_tour_stops', ['tour_id' => $tour->id, 'title' => 'Markt 1, 53111 Bonn', 'position' => 2, 'service_minutes' => 45]);
        $this->assertSame(75, $tour->fresh()->planned_service_minutes);
    }

    public function test_a_stop_on_an_existing_tour_can_be_updated(): void
    {
        config(['services.tomtom.key' => 'test-key']);
        Http::fake(['https://api.tomtom.com/routing/1/calculateRoute/*' => Http::response(['routes' => [[
            'summary' => ['lengthInMeters' => 15000, 'travelTimeInSeconds' => 1800], 'legs' => [],
        ]]])]);
        $user = $this->sckUser();
        [$tour, $stop] = $this->tourAndStop($user);

        $this->actingAs($user)->put(route('sck.routen.stopps.update', $stop), [
            'title' => 'Bearbeiteter Stopp', 'show_stop_address' => 'Hauptstraße 1, Bonn',
            'type' => 'inspection', 'service_minutes' => 45, 'priority' => 5,
            'window_start' => '09:00', 'window_end' => '11:00', 'notes' => 'Klingeln',
        ])->assertRedirect(route('sck.routen.show', $tour));

        $this->assertDatabaseHas('sck_tour_stops', [
            'id' => $stop->id, 'title' => 'Bearbeiteter Stopp', 'type' => 'inspection',
            'service_minutes' => 45, 'priority' => 5,
        ]);
        $this->assertSame(45, $tour->fresh()->planned_service_minutes);
    }

    public function test_map_can_be_selected_as_the_default_sck_app(): void
    {
        $user = $this->sckUser();

        $this->actingAs($user)->post(route('sck.set-default-app'), ['default_app' => 'karte'])->assertRedirect();
        $this->assertSame('karte', $user->fresh()->sck_default_app);
        $this->actingAs($user)->get(route('sck.dashboard'))->assertRedirect(route('sck.map.index'));
    }

    public function test_map_layer_visibility_is_restored_from_the_session(): void
    {
        $user = $this->sckUser();

        $this->actingAs($user)->putJson(route('sck.map.layers.update'), [
            'home' => false, 'customers' => true, 'points' => false, 'tours' => true, 'legend_open' => false,
        ])->assertOk()->assertJsonPath('layers.customers', true)->assertJsonPath('legendOpen', false)
            ->assertSessionHas('sck_map_layers', [
                'home' => false, 'customers' => true, 'points' => false, 'tours' => true,
            ])
            ->assertSessionHas('sck_map_legend_open', false);
    }

    public function test_map_next_tour_prefers_running_and_never_exposes_another_users_tour(): void
    {
        $user = $this->sckUser();
        $other = $this->sckUser();
        [$planned] = $this->tourAndStop($user);
        $planned->update(['title' => 'Nächste geplante Tour', 'tour_date' => today()->addDay(), 'status' => 'planned']);
        [$running] = $this->tourAndStop($user);
        $running->update(['title' => 'Laufende Tour', 'tour_date' => today()->subDay(), 'status' => 'in_progress']);
        [$foreign] = $this->tourAndStop($other);
        $foreign->update(['title' => 'Fremde Tour', 'status' => 'in_progress']);

        $this->actingAs($user)->getJson(route('sck.map.data', ['mode' => 'next']))
            ->assertOk()
            ->assertJsonCount(1, 'tours')
            ->assertJsonPath('tours.0.id', $running->id)
            ->assertJsonMissing(['title' => 'Fremde Tour']);

        $this->actingAs($user)->getJson(route('sck.map.data', ['mode' => 'tour', 'tour_id' => $foreign->id]))
            ->assertNotFound();
    }

    public function test_map_week_payload_contains_routexl_tours_stops_and_stored_route_geometry(): void
    {
        $user = $this->sckUser();
        [$tour, $stop] = $this->tourAndStop($user);
        $tour->update([
            'tour_date' => Carbon::create(2025, 12, 29),
            'route_provider' => 'RouteXL + TomTom',
            'route_optimized' => true,
            'encoded_polyline' => json_encode([['lat' => 50.73, 'lng' => 7.10], ['lat' => 50.74, 'lng' => 7.11]]),
        ]);

        $this->actingAs($user)->getJson(route('sck.map.data', ['mode' => 'week', 'week' => '2026-W01']))
            ->assertOk()
            ->assertJsonPath('tours.0.id', $tour->id)
            ->assertJsonPath('tours.0.provider', 'RouteXL + TomTom')
            ->assertJsonPath('tours.0.optimized', true)
            ->assertJsonPath('tours.0.approximate', false)
            ->assertJsonPath('tours.0.stops.0.id', $stop->id)
            ->assertJsonCount(2, 'tours.0.polyline');
    }

    public function test_shared_map_points_can_be_managed_and_found_by_other_sck_users(): void
    {
        $creator = $this->sckUser();
        $colleague = $this->sckUser();
        $response = $this->actingAs($creator)->postJson(route('sck.map-points.store'), [
            'name' => 'Notfalllager Nord', 'note' => 'Schlüssel beim Pförtner',
            'formatted_address' => 'Hauptstraße 1, 53111 Bonn', 'street' => 'Hauptstraße',
            'house_number' => '1', 'postal_code' => '53111', 'city' => 'Bonn', 'country_code' => 'DE',
            'latitude' => 50.7374, 'longitude' => 7.0982,
        ])->assertCreated();
        $point = SckMapPoint::findOrFail($response->json('point.id'));

        $this->actingAs($colleague)->getJson(route('sck.address-search', ['q' => 'Notfalllager']))
            ->assertOk()->assertJsonPath('results.0.source', 'custom_point')->assertJsonPath('results.0.id', $point->id);

        $this->actingAs($colleague)->putJson(route('sck.map-points.update', $point), [
            'name' => 'Notfalllager West', 'note' => null, 'formatted_address' => null,
            'latitude' => 50.74, 'longitude' => 7.11, 'country_code' => 'DE',
        ])->assertOk()->assertJsonPath('point.name', 'Notfalllager West');

        $this->actingAs($colleague)->deleteJson(route('sck.map-points.destroy', $point))->assertOk();
        $this->assertSoftDeleted('sck_map_points', ['id' => $point->id]);
    }

    public function test_map_reverse_geocoding_returns_structured_tomtom_address(): void
    {
        config(['services.tomtom.key' => 'test-key']);
        Http::fake(['https://api.tomtom.com/search/2/reverseGeocode/*' => Http::response(['addresses' => [[
            'address' => ['freeformAddress' => 'Markt 1, 53111 Bonn', 'streetName' => 'Markt', 'streetNumber' => '1', 'postalCode' => '53111', 'municipality' => 'Bonn', 'countryCode' => 'DE'],
            'position' => ['lat' => 50.735, 'lon' => 7.102],
        ]]])]);

        $this->actingAs($this->sckUser())->getJson(route('sck.map.reverse-geocode', ['lat' => 50.735, 'lng' => 7.102]))
            ->assertOk()->assertJsonPath('result.formatted_address', 'Markt 1, 53111 Bonn')->assertJsonPath('result.city', 'Bonn');
    }

    public function test_customer_changes_are_audited_and_soft_deleted(): void
    {
        $user = $this->sckUser();
        $response = $this->actingAs($user)->post(route('sck.kunden.store'), $this->customerPayload());
        $customer = SckCustomer::firstOrFail();
        $response->assertRedirect(route('sck.kunden.show', $customer));
        $this->assertDatabaseHas('sck_customer_changes', ['customer_id' => $customer->id, 'event' => 'created', 'user_id' => $user->id]);

        $this->actingAs($user)->put(route('sck.kunden.update', $customer), $this->customerPayload(['city' => 'Köln']))->assertRedirect();
        $this->assertDatabaseHas('sck_customer_changes', ['customer_id' => $customer->id, 'event' => 'updated']);
        $this->actingAs($user)->delete(route('sck.kunden.destroy', $customer))->assertRedirect();
        $this->assertSoftDeleted('sck_customers', ['id' => $customer->id]);
    }

    public function test_home_address_is_geocoded_when_route_settings_are_saved(): void
    {
        config(['services.tomtom.key' => 'test-key']);
        Http::fake([
            'https://api.tomtom.com/search/2/search/*' => Http::response(['results' => [[
                'address' => ['freeformAddress' => 'Hauptstraße 1, 53111 Bonn'],
                'position' => ['lat' => 50.7374, 'lon' => 7.0982],
            ]]]),
        ]);
        $user = $this->sckUser();

        $this->actingAs($user)->put(route('sck.routen.settings.update'), $this->routeSettingsPayload())
            ->assertRedirect(route('sck.routen.settings'));

        $this->assertDatabaseHas('sck_route_settings', [
            'user_id' => $user->id, 'home_address' => 'Hauptstraße 1, 53111 Bonn',
            'home_latitude' => 50.7374, 'home_longitude' => 7.0982,
        ]);
    }

    public function test_address_administration_lists_missing_coordinates_and_calculates_them_individually(): void
    {
        config(['services.tomtom.key' => 'test-key']);
        Http::fake([
            'https://api.tomtom.com/search/2/search/*' => Http::response(['results' => [[
                'address' => ['freeformAddress' => 'Hauptstraße 1, 53111 Bonn'],
                'position' => ['lat' => 50.7374, 'lon' => 7.0982],
            ]]]),
        ]);
        $user = $this->sckUser();
        $customer = SckCustomer::create($this->customerPayload());
        $stop = SckStopTemplate::create([
            'title' => 'Wartungsstopp', 'street' => 'Markt', 'house_number' => '2', 'postal_code' => '53111', 'city' => 'Bonn', 'country_code' => 'DE',
        ]);

        $this->actingAs($user)->get(route('sck.administration.addresses.index'))
            ->assertOk()
            ->assertSee('Testkunde')
            ->assertSee('Wartungsstopp');

        $this->actingAs($user)->post(route('sck.administration.addresses.calculate-coordinates', ['customer', $customer]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('sck_customers', ['id' => $customer->id, 'latitude' => 50.7374, 'longitude' => 7.0982]);
        $this->assertDatabaseHas('sck_stop_templates', ['id' => $stop->id, 'latitude' => null, 'longitude' => null]);
    }

    public function test_home_address_save_shows_a_clear_error_when_it_cannot_be_geocoded(): void
    {
        config(['services.tomtom.key' => 'test-key']);
        Http::fake(['https://api.tomtom.com/search/2/search/*' => Http::response(['results' => []])]);
        $user = $this->sckUser();

        $this->from(route('sck.routen.settings'))->actingAs($user)
            ->put(route('sck.routen.settings.update'), $this->routeSettingsPayload(['home_address' => 'Nicht auffindbar 1, 00000 Nirgendwo']))
            ->assertRedirect(route('sck.routen.settings'))
            ->assertSessionHasErrors(['home_address' => 'Die Home-Adresse konnte nicht ermittelt werden. Bitte die vollständige Adresse prüfen und später erneut versuchen.']);

        $this->assertDatabaseMissing('sck_route_settings', ['user_id' => $user->id]);
    }

    public function test_default_datev_export_imports_debtors_and_updates_them_by_account(): void
    {
        $user = $this->sckUser();
        $content = $this->datevCustomerExport([
            $this->datevCustomerRow('10001', 'Kaffeerösterei Müller GmbH', '2', 'Adenauerallee 15a', '53113', 'Bonn', 'DE', '0228 12345', 'info@mueller.test'),
            $this->datevCustomerRow('10002', '', '1', 'Markt 2-4', '50667', 'Köln', 'DE', '0221 9876', 'anna@example.test', 'Mustermann', 'Anna'),
            $this->datevCustomerRow('70001', 'DATEV Lieferant GmbH', '2', 'Industriestraße 8', '53121', 'Bonn', 'DE', '0228 111', 'lieferant@example.test'),
        ], 'Windows-1252');

        $response = $this->actingAs($user)->post(route('sck.kunden.import-datev'), [
            'datev_file' => UploadedFile::fake()->createWithContent('EXTF_Debitoren_Kreditoren.csv', $content),
        ]);

        $response->assertRedirect(route('sck.kunden.index'))->assertSessionHas('success', fn (string $message) => str_contains($message, '2 neu') && str_contains($message, '1 Kreditoren'));
        $this->assertDatabaseCount('sck_customers', 2);
        $this->assertDatabaseHas('sck_customers', [
            'datev_account' => '10001', 'name' => 'Kaffeerösterei Müller GmbH', 'street' => 'Adenauerallee', 'house_number' => '15a',
            'postal_code' => '53113', 'city' => 'Bonn', 'country_code' => 'DE', 'phone' => '0228 12345', 'email' => 'info@mueller.test',
        ]);
        $this->assertDatabaseHas('sck_customers', ['datev_account' => '10002', 'name' => 'Anna Mustermann', 'street' => 'Markt', 'house_number' => '2-4']);
        $this->assertDatabaseMissing('sck_customers', ['datev_account' => '70001']);

        SckCustomer::where('datev_account', '10001')->firstOrFail()->update(['status' => 'blocked']);
        $updated = $this->datevCustomerExport([
            $this->datevCustomerRow('10001', 'Kaffeerösterei Müller GmbH', '2', 'Adenauerallee 15a', '53113', 'Bonn', 'DE', '0228 99999', 'info@mueller.test'),
        ]);
        $this->actingAs($user)->post(route('sck.kunden.import-datev'), [
            'datev_file' => UploadedFile::fake()->createWithContent('DTVF_Debitoren_Kreditoren.csv', $updated),
        ])->assertRedirect(route('sck.kunden.index'));

        $this->assertDatabaseCount('sck_customers', 2);
        $this->assertDatabaseHas('sck_customers', ['datev_account' => '10001', 'phone' => '0228 99999', 'status' => 'blocked']);
    }

    public function test_customer_import_rejects_non_datev_customer_files(): void
    {
        $user = $this->sckUser();

        $this->actingAs($user)->post(route('sck.kunden.import-datev'), [
            'datev_file' => UploadedFile::fake()->createWithContent('Buchungen.csv', "EXTF;700;21;Buchungsstapel\r\n"),
        ])->assertSessionHasErrors('datev_file', null, 'datevImport');

        $this->assertDatabaseCount('sck_customers', 0);
    }

    public function test_manual_usage_snapshots_prices_and_clamps_insufficient_stock(): void
    {
        $user = $this->sckUser();
        [$tour, $stop] = $this->tourAndStop($user);
        $item = SckWarehouseItem::create(['bezeichnung' => 'Pumpe', 'geraet' => 'Jura', 'lieferant' => 'Test', 'ek_ohne_st' => 20, 'vk_ohne_st' => 50, 'stueckzahl' => 2, 'artikelgruppe' => 'Ware', 'einheit' => 'Stück', 'steuersatz' => 19]);

        $this->actingAs($user)->post(route('sck.routen.stopps.items', $stop), ['items' => [['item_id' => $item->id, 'quantity' => 3, 'actual_net_price' => 55]]])->assertRedirect();
        $this->assertSame(0, $item->fresh()->stueckzahl);
        $this->assertDatabaseHas('sck_tour_stop_items', ['tour_stop_id' => $stop->id, 'quantity' => 3, 'stock_deducted' => 2, 'ek_snapshot' => 20, 'actual_net_price' => 55]);
        $this->assertDatabaseHas('sck_warehouse_logs', ['tour_id' => $tour->id, 'tour_stop_id' => $stop->id, 'quantity' => 3]);
    }

    public function test_weekly_planner_generates_three_saved_candidates_without_provider_keys(): void
    {
        config(['services.tomtom.key' => null, 'services.routexl.username' => null, 'services.routexl.password' => null]);
        $user = $this->sckUser();
        SckRouteSetting::create(['user_id' => $user->id, 'home_address' => 'Home', 'home_latitude' => 50.73, 'home_longitude' => 7.10]);
        $plan = SckWeeklyPlan::create(['user_id' => $user->id, 'name' => 'Testwoche', 'week_start' => now()->startOfWeek(), 'tour_count' => 2, 'parameters' => ['enabled_days' => [1, 2], 'default_start' => '08:00', 'max_stops' => 2, 'max_minutes' => 600, 'max_km' => 500]]);
        foreach ([['Nord', 50.8, 7.1], ['Süd', 50.6, 7.1], ['Ost', 50.73, 7.3]] as [$title, $lat, $lng]) $plan->stops()->create(['title' => $title, 'address' => $title, 'latitude' => $lat, 'longitude' => $lng, 'service_minutes' => 30, 'allowed_weekdays' => [1,2], 'priority' => 3]);

        $response = $this->actingAs($user)->postJson(route('sck.wochenplanung.generate', $plan));
        $response->assertOk()->assertJsonCount(3, 'candidates');
        $this->assertDatabaseCount('sck_plan_candidates', 3);
        $this->assertSame(['balanced', 'efficient', 'regions'], $plan->candidates()->orderBy('strategy')->pluck('strategy')->all());
    }

    public function test_private_photo_endpoint_requires_authentication(): void
    {
        Storage::fake('sck_private');
        $user = $this->sckUser(); [, $stop] = $this->tourAndStop($user);
        Storage::disk('sck_private')->put('stops/test.webp', 'private-image');
        $photo = SckStopPhoto::create(['tour_stop_id' => $stop->id, 'user_id' => $user->id, 'path' => 'stops/test.webp', 'original_name' => 'test.webp', 'mime_type' => 'image/webp', 'size' => 13]);
        $this->get(route('sck.media.show', $photo))->assertRedirect('/login');
        $this->actingAs($user)->get(route('sck.media.show', $photo))->assertOk()->assertHeader('Content-Type', 'image/webp');
    }

    public function test_tour_creation_persists_routexl_order_metrics_and_compact_tomtom_geometry(): void
    {
        Cache::flush();
        config([
            'services.routexl.username' => 'route-user',
            'services.routexl.password' => 'route-password',
            'services.routexl.base_url' => 'https://api.routexl.test',
            'services.tomtom.key' => 'tomtom-key',
            'services.tomtom.base_url' => 'https://api.tomtom.test',
        ]);
        $user = $this->sckUser();
        SckRouteSetting::create([
            'user_id' => $user->id, 'home_name' => 'Home', 'home_address' => 'Home',
            'home_latitude' => 50.643298, 'home_longitude' => 9.753112,
        ]);
        $first = SckStopTemplate::create(['title' => 'First', 'latitude' => 50.7, 'longitude' => 9.8, 'service_minutes' => 20]);
        $second = SckStopTemplate::create(['title' => 'Second', 'latitude' => 50.8, 'longitude' => 9.9, 'service_minutes' => 30]);
        $points = [];
        for ($index = 0; $index < 5000; $index++) {
            $points[] = ['latitude' => 50.643298 + $index * 0.00001, 'longitude' => 9.753112 + sin($index / 50) * 0.001];
        }
        Http::fake([
            'https://api.routexl.test/status/sck' => Http::response(['max_locations' => 10]),
            'https://api.routexl.test/tour/' => Http::response([
                'id' => 'test-tour', 'count' => 4, 'feasible' => true,
                'route' => [
                    ['name' => 'route:start', 'arrival' => 0, 'distance' => 0],
                    ['name' => 'stop:'.$second->id, 'arrival' => 45, 'distance' => 31.2],
                    ['name' => 'stop:'.$first->id, 'arrival' => 90, 'distance' => 62.4],
                    ['name' => 'route:end', 'arrival' => 130, 'distance' => 95.8],
                ],
            ]),
            'https://api.tomtom.test/routing/1/calculateRoute/*' => Http::response([
                'routes' => [[
                    'summary' => ['lengthInMeters' => 95800, 'travelTimeInSeconds' => 7800],
                    'legs' => [['points' => $points]],
                ]],
            ]),
        ]);

        $this->actingAs($user)->post(route('sck.routen.store'), [
            'title' => 'Optimized tour',
            'tour_date' => '2026-07-23',
            'departure_time' => '08:00',
            'template_ids' => [$first->id, $second->id],
        ])->assertRedirect();

        $tour = SckTour::where('title', 'Optimized tour')->firstOrFail();
        $this->assertTrue($tour->route_optimized);
        $this->assertSame('RouteXL + TomTom', $tour->route_provider);
        $this->assertStringStartsWith('p5:', $tour->encoded_polyline);
        $this->assertLessThan(65535, strlen($tour->encoded_polyline));
        $this->assertCount(5000, app(RouteGeometryCodec::class)->decode($tour->encoded_polyline));
        $this->assertSame([$second->id, $first->id], $tour->stops->pluck('stop_template_id')->all());
        $this->assertSame(45, $tour->stops[0]->arrival_minutes);
        $this->assertSame('31.20', $tour->stops[0]->cumulative_km);
    }

    public function test_tour_csv_gpx_datev_and_pdf_exports_are_available(): void
    {
        $user = $this->sckUser(); [$tour] = $this->tourAndStop($user);
        SckRouteSetting::create(['user_id' => $user->id]);
        $this->actingAs($user)->get(route('sck.routen.show', $tour))->assertOk();
        $this->actingAs($user)->get(route('sck.routen.export.csv', $tour))->assertOk();
        $this->actingAs($user)->get(route('sck.routen.export.gpx', $tour))->assertOk()->assertHeader('Content-Type', 'application/gpx+xml');
        $this->actingAs($user)->get(route('sck.routen.export.datev', $tour))->assertOk();
        $this->actingAs($user)->get(route('sck.routen.export.pdf', $tour))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    private function sckUser(): User { return User::factory()->create(['role' => 'SCK']); }

    private function customerPayload(array $replace = []): array
    {
        return array_replace(['name' => 'Testkunde', 'street' => 'Hauptstraße', 'house_number' => '1', 'postal_code' => '53111', 'city' => 'Bonn', 'country_code' => 'DE', 'status' => 'active', 'tags' => 'Stammkunde'], $replace);
    }

    private function routeSettingsPayload(array $replace = []): array
    {
        return array_replace([
            'home_name' => 'Büro', 'home_address' => 'Hauptstraße 1, 53111 Bonn',
            'travel_base_fee' => 10, 'travel_per_km' => 0.5, 'travel_per_minute' => 0.2, 'travel_minimum_fee' => 15,
            'internal_per_km' => 0.3, 'internal_per_minute' => 0.1,
            'datev_consultant_number' => '1234', 'datev_client_number' => '123', 'datev_chart' => '04',
            'datev_revenue_19' => '8400', 'datev_revenue_7' => '8300', 'datev_debtor_account' => '10000',
        ], $replace);
    }

    private function tourAndStop(User $user): array
    {
        $tour = SckTour::create(['user_id' => $user->id, 'number' => 'SCK-TEST-'.uniqid(), 'title' => 'Testtour', 'tour_date' => now(), 'status' => 'planned', 'start_snapshot' => ['name' => 'Home', 'address' => 'Home', 'lat' => 50.73, 'lng' => 7.10], 'end_snapshot' => ['name' => 'Home', 'address' => 'Home', 'lat' => 50.73, 'lng' => 7.10], 'planned_km' => 12, 'planned_drive_minutes' => 20, 'planned_service_minutes' => 30, 'travel_fee_pool' => 10, 'internal_travel_cost' => 4]);
        $stop = $tour->stops()->create(['position' => 1, 'title' => 'Kunde', 'address_snapshot' => ['formatted' => 'Hauptstraße 1, Bonn'], 'latitude' => 50.74, 'longitude' => 7.11, 'service_minutes' => 30]);
        return [$tour, $stop];
    }

    private function datevCustomerExport(array $rows, string $encoding = 'UTF-8'): string
    {
        $meta = ['DTVF', 700, 16, 'Debitoren/Kreditoren', 5, '20260722120000000', '', 'RE', '', '', 1001, 1, 20260101, 4];
        $headers = ['Konto', 'Name (Adressatentyp Unternehmen)', 'Unternehmensgegenstand', 'Name (Adressatentyp natürl. Person)', 'Vorname (Adressatentyp natürl. Person)', 'Name (Adressatentyp keine Angabe)', 'Adressatentyp', 'Kurzbezeichnung', 'EU-Land', 'EU-UStID', 'Anrede', 'Titel / Akad. Grad', 'Adelstitel', 'Namensvorsatz', 'Adressart', 'Straße', 'Postfach', 'Postleitzahl', 'Ort', 'Land', 'Versandzusatz', 'Adresszusatz', 'Abweichende Anrede', 'Abw. Zustellbezeichnung 1', 'Abw. Zustellbezeichnung 2', 'Kennz. Korrespondenzadresse', 'Adresse Gültig von', 'Adresse Gültig bis', 'Telefon', 'Bemerkung (Telefon)', 'Telefon Geschäftsleitung', 'Bemerkung (Telefon GL)', 'E-Mail', 'Bemerkung (E-Mail)'];
        $stream = fopen('php://temp', 'w+');
        foreach ([$meta, $headers, ...$rows] as $row) {
            fputcsv($stream, $row, ';', '"', '');
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $encoding === 'UTF-8' ? $content : mb_convert_encoding($content, $encoding, 'UTF-8');
    }

    private function datevCustomerRow(string $account, string $company, string $type, string $street, string $postalCode, string $city, string $country, string $phone, string $email, string $lastName = '', string $firstName = ''): array
    {
        $row = array_fill(0, 34, '');
        foreach ([0 => $account, 1 => $company, 3 => $lastName, 4 => $firstName, 6 => $type, 15 => $street, 17 => $postalCode, 18 => $city, 19 => $country, 28 => $phone, 32 => $email] as $index => $value) {
            $row[$index] = $value;
        }

        return $row;
    }
}

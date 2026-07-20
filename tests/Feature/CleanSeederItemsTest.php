<?php

namespace Tests\Feature;

use App\Models\Sck\SckWarehouseItem;
use Database\Seeders\SckSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanSeederItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_seeder_items_command_removes_only_seeded_items()
    {
        // Seed initial items
        $this->seed(SckSeeder::class);

        // Create a real user item
        $realItem = SckWarehouseItem::create([
            'bezeichnung' => 'Echtes Kundengerät Ersatzteil',
            'geraet' => 'Custom Model X',
            'artikelgruppe' => 'Ersatzteile',
            'einheit' => 'Stück',
            'steuersatz' => '19',
            'lieferant' => 'Real Vendor',
            'ek_ohne_st' => 100.00,
            'vk_ohne_st' => 200.00,
            'alte_artikelnummer' => 'REAL-123',
            'stueckzahl' => 5,
            'kommentar' => 'Created by actual user',
        ]);

        // Run dry-run first
        $this->artisan('db:clean-seeder-items', ['--dry-run' => true])
            ->assertExitCode(0);

        $this->assertDatabaseHas('sck_warehouse_items', ['id' => $realItem->id]);
        $this->assertDatabaseHas('sck_warehouse_items', ['alte_artikelnummer' => 'JU-BG-S9']);

        // Run deletion with --force
        $this->artisan('db:clean-seeder-items', ['--force' => true])
            ->assertExitCode(0);

        // Verify seeded items deleted but real item remains
        $this->assertDatabaseMissing('sck_warehouse_items', ['alte_artikelnummer' => 'JU-BG-S9']);
        $this->assertDatabaseHas('sck_warehouse_items', ['id' => $realItem->id]);
    }
}

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

    public function test_clean_seeder_items_with_detect_batches()
    {
        // We simulate a historical seeder batch by inserting multiple items with the exact same timestamp
        $timestamp = now()->subDays(10)->startOfSecond();

        for ($i = 1; $i <= 10; $i++) {
            $item = new SckWarehouseItem([
                'bezeichnung' => "Historisches Dummy-Item {$i}",
                'geraet' => 'Dummy Gerät',
                'artikelgruppe' => 'Ersatzteile',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Dummy Vendor',
                'ek_ohne_st' => 10.00,
                'vk_ohne_st' => 20.00,
                'alte_artikelnummer' => "DUMMY-OLD-{$i}",
                'stueckzahl' => 1,
            ]);
            $item->created_at = $timestamp;
            $item->updated_at = $timestamp;
            $item->save();
        }

        // Create a real item created at a different time
        $realItem = new SckWarehouseItem([
            'bezeichnung' => 'Echtes Kundengerät',
            'geraet' => 'Custom Model X',
            'artikelgruppe' => 'Ersatzteile',
            'einheit' => 'Stück',
            'steuersatz' => '19',
            'lieferant' => 'Real Vendor',
            'ek_ohne_st' => 100.00,
            'vk_ohne_st' => 200.00,
            'alte_artikelnummer' => 'REAL-999',
            'stueckzahl' => 5,
        ]);
        $realItem->created_at = now()->startOfSecond();
        $realItem->updated_at = now()->startOfSecond();
        $realItem->save();

        // Run detection with --detect-batches and --dry-run
        $this->artisan('db:clean-seeder-items', [
            '--detect-batches' => true,
            '--dry-run' => true
        ])->assertExitCode(0);

        // Verify no items were deleted in dry-run
        $this->assertDatabaseHas('sck_warehouse_items', ['alte_artikelnummer' => 'DUMMY-OLD-1']);
        $this->assertDatabaseHas('sck_warehouse_items', ['id' => $realItem->id]);

        // Run deletion with --detect-batches and --force
        $this->artisan('db:clean-seeder-items', [
            '--detect-batches' => true,
            '--force' => true
        ])->assertExitCode(0);

        // Verify older bulk batch deleted but single real item remains
        $this->assertDatabaseMissing('sck_warehouse_items', ['alte_artikelnummer' => 'DUMMY-OLD-1']);
        $this->assertDatabaseMissing('sck_warehouse_items', ['alte_artikelnummer' => 'DUMMY-OLD-10']);
        $this->assertDatabaseHas('sck_warehouse_items', ['id' => $realItem->id]);
    }

    public function test_clean_seeder_items_with_created_on()
    {
        $targetDate = '2025-05-15';
        $timestamp = date('Y-m-d H:i:s', strtotime($targetDate . ' 14:30:00'));

        // Old dummy item
        $oldItem = new SckWarehouseItem([
            'bezeichnung' => 'Altes Seeder Item',
            'geraet' => 'Dummy',
            'lieferant' => 'Dummy',
            'ek_ohne_st' => 10,
            'vk_ohne_st' => 20,
            'stueckzahl' => 1,
        ]);
        $oldItem->created_at = $timestamp;
        $oldItem->updated_at = $timestamp;
        $oldItem->save();

        // Modern user item
        $newItem = SckWarehouseItem::create([
            'bezeichnung' => 'Echtes Kundengerät',
            'geraet' => 'Custom Model X',
            'lieferant' => 'Real Vendor',
            'ek_ohne_st' => 100.00,
            'vk_ohne_st' => 200.00,
            'stueckzahl' => 5,
        ]);

        // Verify report prints
        $this->artisan('db:clean-seeder-items', ['--report' => true])
            ->assertExitCode(0);

        // Deleting items from specific date
        $this->artisan('db:clean-seeder-items', [
            '--created-on' => $targetDate,
            '--force' => true
        ])->assertExitCode(0);

        $this->assertDatabaseMissing('sck_warehouse_items', ['id' => $oldItem->id]);
        $this->assertDatabaseHas('sck_warehouse_items', ['id' => $newItem->id]);
    }
}

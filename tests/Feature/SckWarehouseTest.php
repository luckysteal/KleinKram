<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Sck\SckWarehouseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SckWarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_datev_export_structure()
    {
        $user = User::factory()->create(['role' => 'SCK']);

        $item = SckWarehouseItem::create([
            'bezeichnung' => 'Brühgruppe Jura',
            'geraet' => 'Jura Impressa',
            'artikelgruppe' => 'Ersatzteil',
            'einheit' => 'Stück',
            'steuersatz' => '19',
            'lieferant' => 'Jura',
            'ek_ohne_st' => 50.00,
            'vk_ohne_st' => 100.00,
            'stueckzahl' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('sck.lager.export-datev'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="DATEV_Artikelimport_' . date('Y-m-d') . '.csv"');

        $content = $response->streamedContent();
        
        // Assert CSV headers and contents match the Netto template CSV
        $this->assertStringContainsString('Art.-Nr.;Bezeichnung;"Zusätzliche Beschreibung";Artikelgruppe;Artikeltyp;Bezeichnung_Steuersatz;Nettopreis;Einheit_(Einzahl);Einheit_(Mehrzahl);Rabattfähig', $content);
        $this->assertStringContainsString('"Brühgruppe Jura";;Ersatzteil;Ware;"Volle Steuer";100,0000;Stück;Stücke;ja', $content);

        // Verify the item is now marked as exported
        $this->assertTrue($item->fresh()->datev_exported);
    }

    public function test_datev_export_excludes_already_exported_items()
    {
        $user = User::factory()->create(['role' => 'SCK']);

        SckWarehouseItem::create([
            'bezeichnung' => 'Exported Item',
            'geraet' => 'Jura Impressa',
            'lieferant' => 'Jura',
            'ek_ohne_st' => 50.00,
            'vk_ohne_st' => 100.00,
            'stueckzahl' => 5,
            'datev_exported' => true,
        ]);

        $response = $this->actingAs($user)->get(route('sck.lager.export-datev'));
        $content = $response->streamedContent();
        $this->assertStringNotContainsString('Exported Item', $content);
    }

    public function test_toggle_datev_exported()
    {
        $user = User::factory()->create(['role' => 'SCK']);

        $item = SckWarehouseItem::create([
            'bezeichnung' => 'Toggle Item',
            'geraet' => 'Jura Impressa',
            'lieferant' => 'Jura',
            'ek_ohne_st' => 50.00,
            'vk_ohne_st' => 100.00,
            'stueckzahl' => 5,
            'datev_exported' => false,
        ]);

        $response = $this->actingAs($user)->post(route('sck.lager.toggle-datev-exported', $item->id));
        $response->assertRedirect();
        $this->assertTrue($item->fresh()->datev_exported);

        $response = $this->actingAs($user)->post(route('sck.lager.toggle-datev-exported', $item->id));
        $this->assertFalse($item->fresh()->datev_exported);
    }

    public function test_bulk_toggle_datev_exported()
    {
        $user = User::factory()->create(['role' => 'SCK']);

        $item1 = SckWarehouseItem::create([
            'bezeichnung' => 'Item 1',
            'geraet' => 'Jura Impressa',
            'lieferant' => 'Jura',
            'ek_ohne_st' => 50.00,
            'vk_ohne_st' => 100.00,
            'stueckzahl' => 5,
            'datev_exported' => false,
        ]);

        $item2 = SckWarehouseItem::create([
            'bezeichnung' => 'Item 2',
            'geraet' => 'Jura Impressa',
            'lieferant' => 'Jura',
            'ek_ohne_st' => 50.00,
            'vk_ohne_st' => 100.00,
            'stueckzahl' => 5,
            'datev_exported' => false,
        ]);

        $response = $this->actingAs($user)->post(route('sck.lager.bulk-toggle-datev-exported'), [
            'ids' => [$item1->id, $item2->id],
            'status' => 'true'
        ]);

        $response->assertRedirect();
        $this->assertTrue($item1->fresh()->datev_exported);
        $this->assertTrue($item2->fresh()->datev_exported);
    }
}

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

        SckWarehouseItem::create([
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
        
        // Assert CSV headers and contents
        $this->assertStringContainsString('Art.-Nr.;Bezeichnung;Einheit;"Verkaufspreis (Netto)";Steuersatz;Artikelgruppe;Belegtext', $content);
        $this->assertStringContainsString('"Brühgruppe Jura";Stück;100,00;19%;Ersatzteil', $content);
    }
}

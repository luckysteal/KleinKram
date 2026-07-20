<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sck\SckWarehouseItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create default SCK user
        if (!User::where('email', 'andreas@sck.de')->exists()) {
            User::create([
                'name'     => 'Andreas',
                'email'    => 'andreas@sck.de',
                'password' => Hash::make('ak1566'),
                'role'     => 'SCK',
            ]);
        }

        // 2. Create mock items for Lagersystem (Coffee machine and Hair salon repairs)
        $items = [
            // ── Kaffeevollautomaten-Reparaturen ─────────────────────────────────────────────
            [
                'bezeichnung' => 'Brühgruppe komplett',
                'geraet' => 'Jura Impressa S9/XS90',
                'artikelgruppe' => 'Ersatzteile Kaffee',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Jura Gastro',
                'ek_ohne_st' => 45.00,
                'vk_ohne_st' => 89.90,
                'alte_artikelnummer' => 'JU-BG-S9',
                'stueckzahl' => 8,
                'kommentar' => 'Original Jura Ersatzteil, voreingestellt.'
            ],
            [
                'bezeichnung' => 'Mahlwerk V5 mit Motor',
                'geraet' => 'Jura Giga 5 / Giga 6',
                'artikelgruppe' => 'Ersatzteile Kaffee',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Jura Gastro',
                'ek_ohne_st' => 62.00,
                'vk_ohne_st' => 119.50,
                'alte_artikelnummer' => 'JU-MW-V5',
                'stueckzahl' => 5,
                'kommentar' => 'Mahlwerk komplett voreingestellt, leise Version.'
            ],
            [
                'bezeichnung' => 'Dichtungssatz Premium O-Ringe (10er Set)',
                'geraet' => 'Jura / DeLonghi Universal',
                'artikelgruppe' => 'Ersatzteile Kaffee',
                'einheit' => 'Set',
                'steuersatz' => '19',
                'lieferant' => 'Gastromed GmbH',
                'ek_ohne_st' => 2.50,
                'vk_ohne_st' => 9.90,
                'alte_artikelnummer' => 'GM-DS-10',
                'stueckzahl' => 50,
                'kommentar' => 'Rote MVQ Silikondichtungen für Brühgruppe.'
            ],
            [
                'bezeichnung' => 'Vibrationspumpe EX 5 230V 48W',
                'geraet' => 'DeLonghi Magnifica S / ESAM',
                'artikelgruppe' => 'Ersatzteile Kaffee',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Ulka Corp.',
                'ek_ohne_st' => 12.50,
                'vk_ohne_st' => 29.90,
                'alte_artikelnummer' => 'UL-EX5-230',
                'stueckzahl' => 15,
                'kommentar' => 'Standardpumpe für viele Kaffeemaschinen.'
            ],
            [
                'bezeichnung' => 'Thermoblock Erhitzer 230V 1200W',
                'geraet' => 'DeLonghi ECAM Serie',
                'artikelgruppe' => 'Ersatzteile Kaffee',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'DeLonghi Service',
                'ek_ohne_st' => 28.00,
                'vk_ohne_st' => 59.90,
                'alte_artikelnummer' => 'DL-TB-1200',
                'stueckzahl' => 6,
                'kommentar' => 'Thermoblock komplett mit Ø 5mm Anschlüssen.'
            ],
            [
                'bezeichnung' => 'Auslaufventil Messing Upgrade',
                'geraet' => 'Jura E- / F- / S-Serie',
                'artikelgruppe' => 'Ersatzteile Kaffee',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Juraprofi.de',
                'ek_ohne_st' => 4.50,
                'vk_ohne_st' => 14.90,
                'alte_artikelnummer' => 'JP-AV-MS',
                'stueckzahl' => 20,
                'kommentar' => 'Messingauslaufventil, langlebigere Alternative.'
            ],
            [
                'bezeichnung' => 'Flüssigentkalker Premium 1L',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Reinigung & Pflege',
                'einheit' => 'Flasche',
                'steuersatz' => '19',
                'lieferant' => 'Gastromed GmbH',
                'ek_ohne_st' => 3.50,
                'vk_ohne_st' => 12.50,
                'alte_artikelnummer' => 'GM-EK-1L',
                'stueckzahl' => 100,
                'kommentar' => 'Auf Amidosulfonsäurebasis mit Farbindikator.'
            ],
            [
                'bezeichnung' => 'Reinigungstabletten 2g (100er Dose)',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Reinigung & Pflege',
                'einheit' => 'Dose',
                'steuersatz' => '19',
                'lieferant' => 'Gastromed GmbH',
                'ek_ohne_st' => 6.20,
                'vk_ohne_st' => 19.90,
                'alte_artikelnummer' => 'GM-RT-100',
                'stueckzahl' => 40,
                'kommentar' => 'Zur Kaffeefettreinigung der Brühgruppe.'
            ],
            [
                'bezeichnung' => 'Druckschlauch FEP 4x2mm (5m Rolle)',
                'geraet' => 'Jura Impressa Serie',
                'artikelgruppe' => 'Schläuche & Verbindungen',
                'einheit' => 'Rolle',
                'steuersatz' => '19',
                'lieferant' => 'Jura Gastro',
                'ek_ohne_st' => 8.80,
                'vk_ohne_st' => 22.00,
                'alte_artikelnummer' => 'JU-DS-FEP',
                'stueckzahl' => 10,
                'kommentar' => 'Hochdruck-Gewebeschlauch FEP.'
            ],
            [
                'bezeichnung' => 'Servicepauschale Kaffeevollautomat',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Stunde',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 75.00,
                'alte_artikelnummer' => 'DL-SERV-KAFF',
                'stueckzahl' => 0,
                'kommentar' => 'Diagnose, Reinigung, Entkalkung und Dichtungswechsel.'
            ],

            // ── Friseursalon-Kundendienst-Reparaturen ─────────────────────────────────────────────
            [
                'bezeichnung' => 'Einhebel-Mischbatterie Friseurbecken',
                'geraet' => 'Olymp / Welonda Waschbecken',
                'artikelgruppe' => 'Ersatzteile Sanitär',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Olymp Service',
                'ek_ohne_st' => 38.50,
                'vk_ohne_st' => 89.00,
                'alte_artikelnummer' => 'OL-MB-500',
                'stueckzahl' => 12,
                'kommentar' => 'Spezialmischer mit verkürztem Hebel.'
            ],
            [
                'bezeichnung' => 'Friseur-Brauseschlauch 150cm schwarz',
                'geraet' => 'Universal Friseurwaschbecken',
                'artikelgruppe' => 'Ersatzteile Sanitär',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Grohe Professional',
                'ek_ohne_st' => 9.20,
                'vk_ohne_st' => 24.90,
                'alte_artikelnummer' => 'GR-BS-150',
                'stueckzahl' => 25,
                'kommentar' => 'Besonders flexibler und robuster Textilschlauch.'
            ],
            [
                'bezeichnung' => 'Profi-Brausekopf schwarz mit Sparventil',
                'geraet' => 'Universal Friseurwaschbecken',
                'artikelgruppe' => 'Ersatzteile Sanitär',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Grohe Professional',
                'ek_ohne_st' => 14.00,
                'vk_ohne_st' => 34.90,
                'alte_artikelnummer' => 'GR-BK-SCHW',
                'stueckzahl' => 18,
                'kommentar' => 'Strahlregler mit Antikalk-System.'
            ],
            [
                'bezeichnung' => 'Ablaufkelch mit Haarsieb Edelstahl',
                'geraet' => 'Welonda / Olymp Becken',
                'artikelgruppe' => 'Ersatzteile Sanitär',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Olymp Service',
                'ek_ohne_st' => 8.50,
                'vk_ohne_st' => 19.90,
                'alte_artikelnummer' => 'OL-AK-ES',
                'stueckzahl' => 15,
                'kommentar' => 'Ablaufventil inklusive Gummidichtung.'
            ],
            [
                'bezeichnung' => 'Haarfangsieb Kunststoff weiß',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Verbrauchsmaterial Sanitär',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Sanitär Großhandel',
                'ek_ohne_st' => 0.40,
                'vk_ohne_st' => 1.99,
                'alte_artikelnummer' => 'SH-HFS-W',
                'stueckzahl' => 150,
                'kommentar' => 'Haarfangsieb, einfach austauschbar.'
            ],
            [
                'bezeichnung' => 'Flexibler Raumsparsifon Ø40mm',
                'geraet' => 'Olymp / Welonda Becken',
                'artikelgruppe' => 'Ersatzteile Sanitär',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Geberit AG',
                'ek_ohne_st' => 7.80,
                'vk_ohne_st' => 19.90,
                'alte_artikelnummer' => 'GE-RSS-40',
                'stueckzahl' => 10,
                'kommentar' => 'Platzsparend für enge Waschbeckenschränke.'
            ],
            [
                'bezeichnung' => 'Sanitär-Silikon Transparent 310ml',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Verbrauchsmaterial Sanitär',
                'einheit' => 'Kartusche',
                'steuersatz' => '19',
                'lieferant' => 'Soudal NV',
                'ek_ohne_st' => 2.90,
                'vk_ohne_st' => 7.90,
                'alte_artikelnummer' => 'SD-SIL-SAN',
                'stueckzahl' => 30,
                'kommentar' => 'Fungizid pilzhemmend eingestellt.'
            ],
            [
                'bezeichnung' => 'Nackenkissen Gummi schwarz',
                'geraet' => 'Olymp Friseurbecken',
                'artikelgruppe' => 'Ersatzteile Sanitär',
                'einheit' => 'Stück',
                'steuersatz' => '19',
                'lieferant' => 'Olymp Service',
                'ek_ohne_st' => 11.20,
                'vk_ohne_st' => 29.90,
                'alte_artikelnummer' => 'OL-NK-SCHW',
                'stueckzahl' => 8,
                'kommentar' => 'Ergonomische Nackenauflage aus Gummi.'
            ],
            [
                'bezeichnung' => 'Servicepauschale Friseursalon Montage',
                'geraet' => 'Waschbecken',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Stunde',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 85.00,
                'alte_artikelnummer' => 'DL-SERV-WASCH',
                'stueckzahl' => 0,
                'kommentar' => 'Montage und installation der Sanitäranschlüsse.'
            ],

            // ── Dienstleistungen ──────────────────────────────────────────────────────────────────
            [
                'bezeichnung' => 'Diagnose & Fehlersuche',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Stunde',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 65.00,
                'alte_artikelnummer' => 'DL-DIAG',
                'stueckzahl' => 0,
                'kommentar' => 'Technische Fehlerdiagnose für Kaffeemaschinen und Sanitäranlagen.'
            ],
            [
                'bezeichnung' => 'Entkalkung & Grundreinigung',
                'geraet' => 'Universal Kaffeevollautomat',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Stunde',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 55.00,
                'alte_artikelnummer' => 'DL-ENT-KAFFEE',
                'stueckzahl' => 0,
                'kommentar' => 'Professionelle Entkalkung inkl. Systemspülung und Funktionstest.'
            ],
            [
                'bezeichnung' => 'Brühgruppen-Überholung',
                'geraet' => 'Jura / DeLonghi',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Pauschale',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 120.00,
                'alte_artikelnummer' => 'DL-BG-UEBER',
                'stueckzahl' => 0,
                'kommentar' => 'Komplette Demontage, Reinigung, Dichtungswechsel und Justierung der Brühgruppe.'
            ],
            [
                'bezeichnung' => 'Mahlwerk-Einstellung & Kalibrierung',
                'geraet' => 'Universal Kaffeevollautomat',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Pauschale',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 45.00,
                'alte_artikelnummer' => 'DL-MW-KAL',
                'stueckzahl' => 0,
                'kommentar' => 'Mahlgradseinstellung und Kaffeepulvermenge nach Kundenwunsch.'
            ],
            [
                'bezeichnung' => 'Anfahrtspauschale (bis 20 km)',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Pauschale',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 25.00,
                'alte_artikelnummer' => 'DL-ANFAHRT-20',
                'stueckzahl' => 0,
                'kommentar' => 'Anfahrtskosten für Vor-Ort-Einsätze bis 20 km Radius.'
            ],
            [
                'bezeichnung' => 'Anfahrtspauschale (21–50 km)',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Pauschale',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 45.00,
                'alte_artikelnummer' => 'DL-ANFAHRT-50',
                'stueckzahl' => 0,
                'kommentar' => 'Anfahrtskosten für Vor-Ort-Einsätze von 21 bis 50 km Radius.'
            ],
            [
                'bezeichnung' => 'Sanitär-Rohrinspektion & Spülung',
                'geraet' => 'Friseurwaschbecken',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Pauschale',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 95.00,
                'alte_artikelnummer' => 'DL-ROHR-INSPEKTION',
                'stueckzahl' => 0,
                'kommentar' => 'Inspektion und Druckspülung der Zu- und Ablaufleitungen am Friseurbecken.'
            ],
            [
                'bezeichnung' => 'Inbetriebnahme & Einweisung',
                'geraet' => 'Universal',
                'artikelgruppe' => 'Dienstleistung',
                'einheit' => 'Stunde',
                'steuersatz' => '19',
                'lieferant' => 'Eigenleistung',
                'ek_ohne_st' => 0.00,
                'vk_ohne_st' => 60.00,
                'alte_artikelnummer' => 'DL-IBN',
                'stueckzahl' => 0,
                'kommentar' => 'Erstinbetriebnahme von Geräten und Einweisung des Kundenpersonals.'
            ],
        ];

        foreach ($items as $item) {
            if (!SckWarehouseItem::where('bezeichnung', $item['bezeichnung'])->exists()) {
                SckWarehouseItem::create($item);
            }
        }
    }
}

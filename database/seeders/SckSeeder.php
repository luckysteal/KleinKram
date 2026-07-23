<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sck\SckCustomer;
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
        User::firstOrCreate(
            ['email' => 'andreas@sck.de'],
            [
                'name'     => 'Andreas',
                'password' => Hash::make('ak1566'),
                'role'     => 'SCK',
            ],
        );

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

        // 3. Create realistic demo customers at real German locations.
        //    Coordinates are stored here from the resolved locations so seeding never calls TomTom.
        //    The local service area is centered on Fulda, with customers spread across Germany.
        $customers = [
            ['datev_account' => '10001', 'name' => 'Kaffeewerk Fulda GmbH', 'street' => 'Universitätsplatz', 'house_number' => '1', 'postal_code' => '36037', 'city' => 'Fulda', 'country_code' => 'DE', 'latitude' => 50.55441, 'longitude' => 9.67814, 'phone' => '0661 555010', 'email' => 'service@kaffeewerk-fulda.example', 'status' => 'active', 'tags' => ['Kaffee', 'Stammkunde'], 'notes' => 'Zwei Gastrogeräte; Wartung bevorzugt vor 10 Uhr.'],
            ['datev_account' => '10002', 'name' => 'Salon Barockstadt', 'street' => 'Friedrichstraße', 'house_number' => '3', 'postal_code' => '36037', 'city' => 'Fulda', 'country_code' => 'DE', 'latitude' => 50.55310, 'longitude' => 9.67568, 'phone' => '0661 555021', 'email' => 'kontakt@salon-barockstadt.example', 'status' => 'active', 'tags' => ['Friseur', 'Sanitär']],
            ['datev_account' => '10003', 'name' => 'Bistro am Dom', 'street' => 'Domplatz', 'house_number' => '1', 'postal_code' => '36037', 'city' => 'Fulda', 'country_code' => 'DE', 'latitude' => 50.55533, 'longitude' => 9.67521, 'phone' => '0661 555032', 'email' => 'technik@bistro-dom.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10004', 'name' => 'Praxis Künzeller Höhe', 'street' => 'Turmstraße', 'house_number' => '3', 'postal_code' => '36093', 'city' => 'Künzell', 'country_code' => 'DE', 'latitude' => 50.54306, 'longitude' => 9.71619, 'phone' => '0661 555043', 'email' => 'praxis@kuenzeller-hoehe.example', 'status' => 'active', 'tags' => ['Sanitär', 'Wartungsvertrag']],
            ['datev_account' => '10005', 'name' => 'Landmarkt Petersberg', 'street' => 'Rathausplatz', 'house_number' => '1', 'postal_code' => '36100', 'city' => 'Petersberg', 'country_code' => 'DE', 'latitude' => 50.55691, 'longitude' => 9.71388, 'phone' => '0661 555054', 'email' => 'buero@landmarkt-petersberg.example', 'status' => 'active', 'tags' => ['Kaffee', 'Einzelhandel']],
            ['datev_account' => '10006', 'name' => 'Eichenzeller Hofküche', 'street' => 'Schlossgasse', 'house_number' => '4', 'postal_code' => '36124', 'city' => 'Eichenzell', 'country_code' => 'DE', 'latitude' => 50.49685, 'longitude' => 9.69716, 'phone' => '06659 555065', 'email' => 'verwaltung@hofkueche-eichenzell.example', 'status' => 'inactive', 'tags' => ['Kaffee', 'Gastronomie'], 'notes' => 'Filiale vorübergehend geschlossen.'],
            ['datev_account' => '10007', 'name' => 'Bäckerei Kaliberg', 'street' => 'Fuldaer Straße', 'house_number' => '2', 'postal_code' => '36119', 'city' => 'Neuhof', 'country_code' => 'DE', 'latitude' => 50.45489, 'longitude' => 9.61713, 'phone' => '06655 555076', 'email' => 'kontakt@baeckerei-kaliberg.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10008', 'name' => 'Hünfelder Büroservice', 'street' => 'Konrad-Adenauer-Platz', 'house_number' => '1', 'postal_code' => '36088', 'city' => 'Hünfeld', 'country_code' => 'DE', 'latitude' => 50.67931, 'longitude' => 9.76701, 'phone' => '06652 555087', 'email' => 'buero@huenfelder-service.example', 'status' => 'active', 'tags' => ['Kaffee', 'Büro']],
            ['datev_account' => '10009', 'name' => 'Schlitzer Rösthaus', 'street' => 'An der Kirche', 'house_number' => '1', 'postal_code' => '36110', 'city' => 'Schlitz', 'country_code' => 'DE', 'latitude' => 50.67494, 'longitude' => 9.56138, 'phone' => '06642 555098', 'email' => 'hallo@schlitzer-roesthaus.example', 'status' => 'active', 'tags' => ['Kaffee']],
            ['datev_account' => '10010', 'name' => 'Café Stiftsbezirk', 'street' => 'Linggplatz', 'house_number' => '12', 'postal_code' => '36251', 'city' => 'Bad Hersfeld', 'country_code' => 'DE', 'latitude' => 50.86860, 'longitude' => 9.70876, 'phone' => '06621 555109', 'email' => 'service@cafe-stiftsbezirk.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10011', 'name' => 'Vogelsberg Friseuratelier', 'street' => 'Marktplatz', 'house_number' => '1', 'postal_code' => '36341', 'city' => 'Lauterbach', 'country_code' => 'DE', 'latitude' => 50.63508, 'longitude' => 9.39712, 'phone' => '06641 555110', 'email' => 'atelier@vogelsberg-friseur.example', 'status' => 'active', 'tags' => ['Friseur']],
            ['datev_account' => '10012', 'name' => 'Kinzigtal Hotelservice', 'street' => 'Unter den Linden', 'house_number' => '13', 'postal_code' => '36381', 'city' => 'Schlüchtern', 'country_code' => 'DE', 'latitude' => 50.34864, 'longitude' => 9.52507, 'phone' => '06661 555121', 'email' => 'technik@kinzig-hotelservice.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10013', 'name' => 'Hafenkante Hamburg', 'street' => 'Bei den St. Pauli Landungsbrücken', 'house_number' => '3', 'postal_code' => '20359', 'city' => 'Hamburg', 'country_code' => 'DE', 'latitude' => 53.54631, 'longitude' => 9.96648, 'phone' => '040 555131', 'email' => 'service@hafenkante-hamburg.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10014', 'name' => 'Spreebogen Büroservice', 'street' => 'Friedrichstraße', 'house_number' => '140', 'postal_code' => '10117', 'city' => 'Berlin', 'country_code' => 'DE', 'latitude' => 52.52049, 'longitude' => 13.38685, 'phone' => '030 555141', 'email' => 'buero@spreebogen.example', 'status' => 'active', 'tags' => ['Kaffee', 'Büro']],
            ['datev_account' => '10015', 'name' => 'Isartor Kaffeebar', 'street' => 'Tal', 'house_number' => '43', 'postal_code' => '80331', 'city' => 'München', 'country_code' => 'DE', 'latitude' => 48.13548, 'longitude' => 11.58029, 'phone' => '089 555151', 'email' => 'hallo@isartor-kaffeebar.example', 'status' => 'active', 'tags' => ['Kaffee']],
            ['datev_account' => '10016', 'name' => 'Neckar Salon', 'street' => 'Königstraße', 'house_number' => '1A', 'postal_code' => '70173', 'city' => 'Stuttgart', 'country_code' => 'DE', 'latitude' => 48.77853, 'longitude' => 9.17949, 'phone' => '0711 555161', 'email' => 'kontakt@neckar-salon.example', 'status' => 'active', 'tags' => ['Friseur']],
            ['datev_account' => '10017', 'name' => 'Elbflorenz Cafébetrieb', 'street' => 'Theaterplatz', 'house_number' => '1', 'postal_code' => '01067', 'city' => 'Dresden', 'country_code' => 'DE', 'latitude' => 51.05395, 'longitude' => 13.73578, 'phone' => '0351 555171', 'email' => 'service@elbflorenz-cafe.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10018', 'name' => 'Leipzig Mitte Praxis', 'street' => 'Markt', 'house_number' => '1', 'postal_code' => '04109', 'city' => 'Leipzig', 'country_code' => 'DE', 'latitude' => 51.34025, 'longitude' => 12.37475, 'phone' => '0341 555181', 'email' => 'praxis@leipzig-mitte.example', 'status' => 'active', 'tags' => ['Sanitär', 'Wartungsvertrag']],
            ['datev_account' => '10019', 'name' => 'Maschsee Gastronomie', 'street' => 'Arthur-Menge-Ufer', 'house_number' => '3', 'postal_code' => '30169', 'city' => 'Hannover', 'country_code' => 'DE', 'latitude' => 52.35105, 'longitude' => 9.74140, 'phone' => '0511 555191', 'email' => 'technik@maschsee-gastronomie.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10020', 'name' => 'Weser Kontor', 'street' => 'Am Markt', 'house_number' => '1', 'postal_code' => '28195', 'city' => 'Bremen', 'country_code' => 'DE', 'latitude' => 53.07582, 'longitude' => 8.80716, 'phone' => '0421 555201', 'email' => 'buero@weser-kontor.example', 'status' => 'active', 'tags' => ['Kaffee', 'Büro']],
            ['datev_account' => '10021', 'name' => 'Prinzipalmarkt Rösterei', 'street' => 'Prinzipalmarkt', 'house_number' => '10', 'postal_code' => '48143', 'city' => 'Münster', 'country_code' => 'DE', 'latitude' => 51.96245, 'longitude' => 7.62850, 'phone' => '0251 555211', 'email' => 'hallo@prinzipalmarkt-roesterei.example', 'status' => 'active', 'tags' => ['Kaffee']],
            ['datev_account' => '10022', 'name' => 'Mainufer Service', 'street' => 'Mainkai', 'house_number' => '35', 'postal_code' => '60311', 'city' => 'Frankfurt am Main', 'country_code' => 'DE', 'latitude' => 50.10939, 'longitude' => 8.68175, 'phone' => '069 555221', 'email' => 'service@mainufer.example', 'status' => 'active', 'tags' => ['Sanitär', 'Wartungsvertrag']],
            ['datev_account' => '10023', 'name' => 'Altstadt Nürnberg Café', 'street' => 'Hauptmarkt', 'house_number' => '18', 'postal_code' => '90403', 'city' => 'Nürnberg', 'country_code' => 'DE', 'latitude' => 49.45428, 'longitude' => 11.07786, 'phone' => '0911 555231', 'email' => 'service@altstadt-nuernberg.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
            ['datev_account' => '10024', 'name' => 'Münsterplatz Friseuratelier', 'street' => 'Münsterplatz', 'house_number' => '24', 'postal_code' => '79098', 'city' => 'Freiburg im Breisgau', 'country_code' => 'DE', 'latitude' => 47.99539, 'longitude' => 7.85248, 'phone' => '0761 555241', 'email' => 'atelier@muensterplatz-freiburg.example', 'status' => 'active', 'tags' => ['Friseur']],
            ['datev_account' => '10025', 'name' => 'Warnow Kaffeekontor', 'street' => 'Neuer Markt', 'house_number' => '1', 'postal_code' => '18055', 'city' => 'Rostock', 'country_code' => 'DE', 'latitude' => 54.08851, 'longitude' => 12.14058, 'phone' => '0381 555251', 'email' => 'hallo@warnow-kontor.example', 'status' => 'active', 'tags' => ['Kaffee']],
            ['datev_account' => '10026', 'name' => 'Saarbrücker Servicepunkt', 'street' => 'St. Johanner Markt', 'house_number' => '1', 'postal_code' => '66111', 'city' => 'Saarbrücken', 'country_code' => 'DE', 'latitude' => 49.23585, 'longitude' => 6.99649, 'phone' => '0681 555261', 'email' => 'service@saarbruecker-servicepunkt.example', 'status' => 'active', 'tags' => ['Kaffee', 'Büro']],
            ['datev_account' => '10027', 'name' => 'Aachener Domcafé', 'street' => 'Hof', 'house_number' => '1', 'postal_code' => '52062', 'city' => 'Aachen', 'country_code' => 'DE', 'latitude' => 50.77487, 'longitude' => 6.08356, 'phone' => '0241 555271', 'email' => 'service@aachener-domcafe.example', 'status' => 'active', 'tags' => ['Kaffee', 'Gastronomie']],
        ];

        foreach ($customers as $customer) {
            SckCustomer::updateOrCreate(['datev_account' => $customer['datev_account']], $customer);
        }
    }
}

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

        // 2. Create ~200 mock items for Lagersystem
        $items = [
            // ── Verbrauchsmaterial ─────────────────────────────────────────────
            ['bezeichnung' => 'Bohrschrauben 4.8×19', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 4.50, 'vk_ohne_st' => 9.99, 'alte_artikelnummer' => 'W-4819-B', 'stueckzahl' => 150, 'kommentar' => 'VPE 100 Stk. Regal A3'],
            ['bezeichnung' => 'Bohrschrauben 5.5×25', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 5.20, 'vk_ohne_st' => 11.50, 'alte_artikelnummer' => 'W-5525-B', 'stueckzahl' => 200, 'kommentar' => 'VPE 100 Stk. Regal A3'],
            ['bezeichnung' => 'Senkkopfschrauben M5×30', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 3.10, 'vk_ohne_st' => 6.99, 'alte_artikelnummer' => 'W-SK-M530', 'stueckzahl' => 300, 'kommentar' => 'Edelstahl A2'],
            ['bezeichnung' => 'Sechskantschrauben M8×50', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 4.80, 'vk_ohne_st' => 10.50, 'alte_artikelnummer' => 'W-HX-M850', 'stueckzahl' => 120, 'kommentar' => 'DIN 933, galvanisch verzinkt'],
            ['bezeichnung' => 'Muttern M8 DIN 934', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 2.30, 'vk_ohne_st' => 4.90, 'alte_artikelnummer' => 'W-NUT-M8', 'stueckzahl' => 500, 'kommentar' => 'VPE 100 Stk.'],
            ['bezeichnung' => 'Unterlegscheiben M8', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 1.80, 'vk_ohne_st' => 3.90, 'alte_artikelnummer' => 'W-ULW-M8', 'stueckzahl' => 600, 'kommentar' => 'DIN 125'],
            ['bezeichnung' => 'Dübel S10 Universal', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Fischer GmbH', 'ek_ohne_st' => 5.50, 'vk_ohne_st' => 12.00, 'alte_artikelnummer' => 'FI-S10', 'stueckzahl' => 400, 'kommentar' => 'VPE 50 Stk., für alle Baustoffe'],
            ['bezeichnung' => 'Dübel S6 Universal', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Fischer GmbH', 'ek_ohne_st' => 3.20, 'vk_ohne_st' => 6.50, 'alte_artikelnummer' => 'FI-S6', 'stueckzahl' => 800, 'kommentar' => 'VPE 100 Stk.'],
            ['bezeichnung' => 'Kabelbinder 200×3,5 schwarz', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Hellermann Tyton', 'ek_ohne_st' => 4.10, 'vk_ohne_st' => 8.50, 'alte_artikelnummer' => 'HT-CB200B', 'stueckzahl' => 1000, 'kommentar' => 'VPE 100 Stk. UV-stabilisiert'],
            ['bezeichnung' => 'Kabelbinder 300×4,8 natur', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Hellermann Tyton', 'ek_ohne_st' => 5.60, 'vk_ohne_st' => 11.90, 'alte_artikelnummer' => 'HT-CB300N', 'stueckzahl' => 500, 'kommentar' => 'VPE 100 Stk.'],
            ['bezeichnung' => 'Iso-Band schwarz 19mm', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Tesa SE', 'ek_ohne_st' => 1.20, 'vk_ohne_st' => 2.50, 'alte_artikelnummer' => 'TS-ISO-19', 'stueckzahl' => 60, 'kommentar' => '20m Rolle, PVC'],
            ['bezeichnung' => 'Panzerband grau 50mm', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Tesa SE', 'ek_ohne_st' => 2.80, 'vk_ohne_st' => 5.99, 'alte_artikelnummer' => 'TS-PB-50', 'stueckzahl' => 30, 'kommentar' => '50m Rolle'],
            ['bezeichnung' => 'Silikon-Dichtmasse grau', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Soudal NV', 'ek_ohne_st' => 3.50, 'vk_ohne_st' => 7.20, 'alte_artikelnummer' => 'SD-SIL-GR', 'stueckzahl' => 25, 'kommentar' => '300ml Kartusche, RTV'],
            ['bezeichnung' => 'PU-Schaum 750ml', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Soudal NV', 'ek_ohne_st' => 4.90, 'vk_ohne_st' => 9.99, 'alte_artikelnummer' => 'SD-PU-750', 'stueckzahl' => 20, 'kommentar' => 'Montageschaum B2'],
            ['bezeichnung' => 'Klebeband transparent 48mm', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Tesa SE', 'ek_ohne_st' => 0.90, 'vk_ohne_st' => 2.00, 'alte_artikelnummer' => 'TS-KL-48T', 'stueckzahl' => 48, 'kommentar' => '66m Rolle'],
            ['bezeichnung' => 'Schleifpapier K80 230×280mm', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Sia Abrasives', 'ek_ohne_st' => 0.45, 'vk_ohne_st' => 1.20, 'alte_artikelnummer' => 'SIA-K80', 'stueckzahl' => 200, 'kommentar' => 'Holz & Metall'],
            ['bezeichnung' => 'Schleifpapier K120 230×280mm', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Sia Abrasives', 'ek_ohne_st' => 0.45, 'vk_ohne_st' => 1.20, 'alte_artikelnummer' => 'SIA-K120', 'stueckzahl' => 200, 'kommentar' => 'Holz & Metall'],
            ['bezeichnung' => 'Schleifpapier K240 230×280mm', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Sia Abrasives', 'ek_ohne_st' => 0.45, 'vk_ohne_st' => 1.20, 'alte_artikelnummer' => 'SIA-K240', 'stueckzahl' => 150, 'kommentar' => 'Feinschliff'],
            ['bezeichnung' => 'Reinigungstücher 38×40cm', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Tork AB', 'ek_ohne_st' => 7.50, 'vk_ohne_st' => 14.90, 'alte_artikelnummer' => 'TK-TW-40', 'stueckzahl' => 15, 'kommentar' => 'VPE 400 Stk., Vliesstoff'],
            ['bezeichnung' => 'Einweghandschuhe Nitril M', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Kimberly-Clark', 'ek_ohne_st' => 8.20, 'vk_ohne_st' => 16.00, 'alte_artikelnummer' => 'KC-NIT-M', 'stueckzahl' => 20, 'kommentar' => 'VPE 100 Stk., puderfrei'],
            ['bezeichnung' => 'Einweghandschuhe Nitril L', 'geraet' => 'Verbrauchsmaterial', 'lieferant' => 'Kimberly-Clark', 'ek_ohne_st' => 8.20, 'vk_ohne_st' => 16.00, 'alte_artikelnummer' => 'KC-NIT-L', 'stueckzahl' => 20, 'kommentar' => 'VPE 100 Stk., puderfrei'],

            // ── Elektrowerkzeug ────────────────────────────────────────────────
            ['bezeichnung' => 'Kabeltrommel 50m H07RN-F', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Schill GmbH', 'ek_ohne_st' => 45.00, 'vk_ohne_st' => 89.50, 'alte_artikelnummer' => 'EL-KT-50', 'stueckzahl' => 12, 'kommentar' => 'DGUV V3 prüfpflichtig'],
            ['bezeichnung' => 'Kabeltrommel 25m H07RN-F', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Schill GmbH', 'ek_ohne_st' => 28.00, 'vk_ohne_st' => 54.90, 'alte_artikelnummer' => 'EL-KT-25', 'stueckzahl' => 8, 'kommentar' => 'Outdoor geeignet'],
            ['bezeichnung' => 'Winkelschleifer Bosch GWS 18V-10', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 119.00, 'vk_ohne_st' => 189.00, 'alte_artikelnummer' => 'BP-GWS18', 'stueckzahl' => 4, 'kommentar' => '18V Solo, Ø 125mm'],
            ['bezeichnung' => 'Stichsäge Bosch GST 18V-57', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 139.00, 'vk_ohne_st' => 219.00, 'alte_artikelnummer' => 'BP-GST18', 'stueckzahl' => 3, 'kommentar' => '18V Solo'],
            ['bezeichnung' => 'Schlagbohrmaschine Bosch GSB 21-2', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 89.00, 'vk_ohne_st' => 139.00, 'alte_artikelnummer' => 'BP-GSB21', 'stueckzahl' => 5, 'kommentar' => '230V Netzbetrieb'],
            ['bezeichnung' => 'Heißluftgebläse Steinel HG 2320', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Steinel GmbH', 'ek_ohne_st' => 48.00, 'vk_ohne_st' => 82.00, 'alte_artikelnummer' => 'ST-HG2320', 'stueckzahl' => 3, 'kommentar' => '2300W, max 630°C'],
            ['bezeichnung' => 'Lötstation Ersa i-CON 1', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Ersa GmbH', 'ek_ohne_st' => 85.00, 'vk_ohne_st' => 139.00, 'alte_artikelnummer' => 'ER-IC1', 'stueckzahl' => 2, 'kommentar' => '80W digital'],
            ['bezeichnung' => 'Kreissäge Makita HS7601', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Makita GmbH', 'ek_ohne_st' => 95.00, 'vk_ohne_st' => 149.00, 'alte_artikelnummer' => 'MK-HS7601', 'stueckzahl' => 2, 'kommentar' => '1200W, Ø 190mm'],
            ['bezeichnung' => 'Industriestaubsauger Makita 447L', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Makita GmbH', 'ek_ohne_st' => 210.00, 'vk_ohne_st' => 329.00, 'alte_artikelnummer' => 'MK-447L', 'stueckzahl' => 2, 'kommentar' => '30L, M-Klasse'],
            ['bezeichnung' => 'Akkuschleifer Festool ETS 125/3 EQ', 'geraet' => 'Elektrowerkzeug', 'lieferant' => 'Festool GmbH', 'ek_ohne_st' => 189.00, 'vk_ohne_st' => 289.00, 'alte_artikelnummer' => 'FT-ETS125', 'stueckzahl' => 2, 'kommentar' => 'Exzenterschleifer 18V'],

            // ── Handwerkzeug ───────────────────────────────────────────────────
            ['bezeichnung' => 'Akkuschrauber GSR 18V-55', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 110.00, 'vk_ohne_st' => 179.00, 'alte_artikelnummer' => 'B-GSR18V', 'stueckzahl' => 8, 'kommentar' => 'Inkl. L-BOXX, ohne Akkus'],
            ['bezeichnung' => 'Akkuschrauber Makita DDF481', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Makita GmbH', 'ek_ohne_st' => 98.00, 'vk_ohne_st' => 159.00, 'alte_artikelnummer' => 'MK-DDF481', 'stueckzahl' => 6, 'kommentar' => '18V Solo'],
            ['bezeichnung' => 'Hammer 300g Schlosserhammer', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Gedore GmbH', 'ek_ohne_st' => 12.50, 'vk_ohne_st' => 22.90, 'alte_artikelnummer' => 'GD-SH300', 'stueckzahl' => 10, 'kommentar' => 'Fiberglasstiel'],
            ['bezeichnung' => 'Hammer 500g Schlosserhammer', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Gedore GmbH', 'ek_ohne_st' => 16.00, 'vk_ohne_st' => 28.50, 'alte_artikelnummer' => 'GD-SH500', 'stueckzahl' => 8, 'kommentar' => 'Fiberglasstiel'],
            ['bezeichnung' => 'Schraubenzieher-Set Wera Kraftform', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Wera GmbH', 'ek_ohne_st' => 28.90, 'vk_ohne_st' => 49.00, 'alte_artikelnummer' => 'WR-KF7', 'stueckzahl' => 6, 'kommentar' => '7-tlg. Satz PH/PZ/SL'],
            ['bezeichnung' => 'Zange Flachzange 200mm', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Knipex GmbH', 'ek_ohne_st' => 14.20, 'vk_ohne_st' => 25.00, 'alte_artikelnummer' => 'KN-FLZ200', 'stueckzahl' => 8, 'kommentar' => 'Vanadium-Stahl'],
            ['bezeichnung' => 'Zange Seitenschneider 180mm', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Knipex GmbH', 'ek_ohne_st' => 13.80, 'vk_ohne_st' => 24.50, 'alte_artikelnummer' => 'KN-SS180', 'stueckzahl' => 6, 'kommentar' => ''],
            ['bezeichnung' => 'Wasserwaage 60cm Aluminium', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Stabila GmbH', 'ek_ohne_st' => 18.00, 'vk_ohne_st' => 32.00, 'alte_artikelnummer' => 'ST-WW60', 'stueckzahl' => 5, 'kommentar' => 'IP54, 3 Libellen'],
            ['bezeichnung' => 'Wasserwaage 120cm Aluminium', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Stabila GmbH', 'ek_ohne_st' => 25.00, 'vk_ohne_st' => 44.00, 'alte_artikelnummer' => 'ST-WW120', 'stueckzahl' => 4, 'kommentar' => 'IP54, 3 Libellen'],
            ['bezeichnung' => 'Gliedermaßstab 2m Holz', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Stanley Black&Decker', 'ek_ohne_st' => 4.50, 'vk_ohne_st' => 8.90, 'alte_artikelnummer' => 'SB-ZM2', 'stueckzahl' => 12, 'kommentar' => '10-gliedrig'],
            ['bezeichnung' => 'Cutter Abbrechmesser 18mm', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Olfa Corp.', 'ek_ohne_st' => 3.50, 'vk_ohne_st' => 6.90, 'alte_artikelnummer' => 'OL-L1', 'stueckzahl' => 15, 'kommentar' => 'Ersatzklingen VPE 10 Stk.'],
            ['bezeichnung' => 'Steckschlüsselsatz 1/2" 19-tlg.', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Gedore GmbH', 'ek_ohne_st' => 48.00, 'vk_ohne_st' => 79.00, 'alte_artikelnummer' => 'GD-SS19', 'stueckzahl' => 4, 'kommentar' => '10–32mm'],
            ['bezeichnung' => 'Kneifzange 250mm', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Knipex GmbH', 'ek_ohne_st' => 19.90, 'vk_ohne_st' => 34.00, 'alte_artikelnummer' => 'KN-KZ250', 'stueckzahl' => 5, 'kommentar' => ''],
            ['bezeichnung' => 'Handhebelstanze 5-tlg. Set', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Stanley Black&Decker', 'ek_ohne_st' => 22.00, 'vk_ohne_st' => 39.00, 'alte_artikelnummer' => 'SB-HHS5', 'stueckzahl' => 3, 'kommentar' => 'Ø 6–25mm'],
            ['bezeichnung' => 'Spachtelmesser 100mm Flex', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Bahco AB', 'ek_ohne_st' => 5.20, 'vk_ohne_st' => 9.90, 'alte_artikelnummer' => 'BH-SP100', 'stueckzahl' => 10, 'kommentar' => 'Federstahl'],
            ['bezeichnung' => 'Rohrschlüssel Stillson 14"', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Gedore GmbH', 'ek_ohne_st' => 22.00, 'vk_ohne_st' => 38.90, 'alte_artikelnummer' => 'GD-RS14', 'stueckzahl' => 4, 'kommentar' => '0–50mm'],
            ['bezeichnung' => 'Sägeblatt HCS 300mm T101B', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 3.20, 'vk_ohne_st' => 6.50, 'alte_artikelnummer' => 'BP-T101B', 'stueckzahl' => 50, 'kommentar' => 'VPE 5 Stk. Holz-Schnitt'],
            ['bezeichnung' => 'Trennscheibe 125mm A30', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Pferd GmbH', 'ek_ohne_st' => 1.90, 'vk_ohne_st' => 3.99, 'alte_artikelnummer' => 'PF-TRS125', 'stueckzahl' => 100, 'kommentar' => 'Metall, 1.0mm dünn'],
            ['bezeichnung' => 'Schleifscheibe 125mm K60', 'geraet' => 'Handwerkzeug', 'lieferant' => 'Pferd GmbH', 'ek_ohne_st' => 1.60, 'vk_ohne_st' => 3.20, 'alte_artikelnummer' => 'PF-SS125-60', 'stueckzahl' => 80, 'kommentar' => 'Klettverschluss'],

            // ── Lichttechnik ───────────────────────────────────────────────────
            ['bezeichnung' => 'LED Hallenstrahler 150W', 'geraet' => 'Lichttechnik', 'lieferant' => 'Osram AG', 'ek_ohne_st' => 65.20, 'vk_ohne_st' => 119.00, 'alte_artikelnummer' => 'LH-150-LED', 'stueckzahl' => 24, 'kommentar' => '4000K, IP65'],
            ['bezeichnung' => 'LED Hallenstrahler 200W', 'geraet' => 'Lichttechnik', 'lieferant' => 'Osram AG', 'ek_ohne_st' => 85.00, 'vk_ohne_st' => 149.00, 'alte_artikelnummer' => 'LH-200-LED', 'stueckzahl' => 12, 'kommentar' => '4000K, IP65'],
            ['bezeichnung' => 'LED Arbeitsleuchte Bosch GLI 18V-1900', 'geraet' => 'Lichttechnik', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 55.00, 'vk_ohne_st' => 89.00, 'alte_artikelnummer' => 'BP-GLI18', 'stueckzahl' => 6, 'kommentar' => '18V, 1900 Lumen Solo'],
            ['bezeichnung' => 'Taschenlampe Peli 3315', 'geraet' => 'Lichttechnik', 'lieferant' => 'Peli Products', 'ek_ohne_st' => 24.00, 'vk_ohne_st' => 42.00, 'alte_artikelnummer' => 'PL-3315', 'stueckzahl' => 8, 'kommentar' => 'ATEX Zone 0, LED'],
            ['bezeichnung' => 'Stirnlampe Petzl Tikka', 'geraet' => 'Lichttechnik', 'lieferant' => 'Petzl ARRE', 'ek_ohne_st' => 16.50, 'vk_ohne_st' => 28.90, 'alte_artikelnummer' => 'PZ-TIKKA', 'stueckzahl' => 10, 'kommentar' => '300 Lumen, IPX4'],
            ['bezeichnung' => 'Baustrahler 500W Halogen', 'geraet' => 'Lichttechnik', 'lieferant' => 'Brennenstuhl GmbH', 'ek_ohne_st' => 18.00, 'vk_ohne_st' => 32.00, 'alte_artikelnummer' => 'BR-BS500', 'stueckzahl' => 6, 'kommentar' => 'IP44, mit Stativ'],
            ['bezeichnung' => 'LED Baustrahler 50W', 'geraet' => 'Lichttechnik', 'lieferant' => 'Brennenstuhl GmbH', 'ek_ohne_st' => 35.00, 'vk_ohne_st' => 59.00, 'alte_artikelnummer' => 'BR-LBS50', 'stueckzahl' => 8, 'kommentar' => '5000 Lumen, IP65'],
            ['bezeichnung' => 'Notleuchte LED 8W Akku', 'geraet' => 'Lichttechnik', 'lieferant' => 'Legrand SA', 'ek_ohne_st' => 42.00, 'vk_ohne_st' => 72.00, 'alte_artikelnummer' => 'LG-NL8W', 'stueckzahl' => 10, 'kommentar' => '3h Autonomie, EN 60598-2-22'],
            ['bezeichnung' => 'Lichterkette LED 20m', 'geraet' => 'Lichttechnik', 'lieferant' => 'Osram AG', 'ek_ohne_st' => 19.90, 'vk_ohne_st' => 34.00, 'alte_artikelnummer' => 'OS-LE20', 'stueckzahl' => 4, 'kommentar' => 'IP44, Außenbereich'],

            // ── Schutzausrüstung (PSA) ─────────────────────────────────────────
            ['bezeichnung' => 'Schutzhelm Uvex Pheos', 'geraet' => 'Schutzausrüstung', 'lieferant' => 'Uvex Safety GmbH', 'ek_ohne_st' => 15.00, 'vk_ohne_st' => 28.00, 'alte_artikelnummer' => 'UV-PH-WH', 'stueckzahl' => 15, 'kommentar' => 'EN 397, weiß'],
            ['bezeichnung' => 'Sicherheitsschuhe S3 Uvex 8544', 'geraet' => 'Schutzausrüstung', 'lieferant' => 'Uvex Safety GmbH', 'ek_ohne_st' => 65.00, 'vk_ohne_st' => 99.00, 'alte_artikelnummer' => 'UV-SS8544', 'stueckzahl' => 6, 'kommentar' => 'Gr. 42, EN ISO 20345'],
            ['bezeichnung' => 'Schutzbrille Uvex Pheos CX2', 'geraet' => 'Schutzausrüstung', 'lieferant' => 'Uvex Safety GmbH', 'ek_ohne_st' => 8.50, 'vk_ohne_st' => 15.90, 'alte_artikelnummer' => 'UV-PHX2', 'stueckzahl' => 20, 'kommentar' => 'EN 166, klar'],
            ['bezeichnung' => 'Gehörschutz 3M 1110', 'geraet' => 'Schutzausrüstung', 'lieferant' => '3M Deutschland GmbH', 'ek_ohne_st' => 0.25, 'vk_ohne_st' => 0.70, 'alte_artikelnummer' => '3M-1110', 'stueckzahl' => 200, 'kommentar' => 'SNR 29dB, Einweg'],
            ['bezeichnung' => 'Atemschutzmaske FFP2 NR 3M 8822', 'geraet' => 'Schutzausrüstung', 'lieferant' => '3M Deutschland GmbH', 'ek_ohne_st' => 1.80, 'vk_ohne_st' => 3.50, 'alte_artikelnummer' => '3M-8822', 'stueckzahl' => 100, 'kommentar' => 'Ventil, zertifiziert'],
            ['bezeichnung' => 'Warnweste EN 471 Klasse 2', 'geraet' => 'Schutzausrüstung', 'lieferant' => 'Engel Workwear', 'ek_ohne_st' => 2.50, 'vk_ohne_st' => 5.50, 'alte_artikelnummer' => 'EW-WW2', 'stueckzahl' => 30, 'kommentar' => 'Gelb, Größe L/XL'],
            ['bezeichnung' => 'Schnittschutzhandschuhe Gr. 10', 'geraet' => 'Schutzausrüstung', 'lieferant' => 'Uvex Safety GmbH', 'ek_ohne_st' => 6.80, 'vk_ohne_st' => 12.90, 'alte_artikelnummer' => 'UV-SHG10', 'stueckzahl' => 15, 'kommentar' => 'Klasse C EN 388'],
            ['bezeichnung' => 'Knieschoner ProKnee Modell P', 'geraet' => 'Schutzausrüstung', 'lieferant' => 'ProKnee Inc.', 'ek_ohne_st' => 38.00, 'vk_ohne_st' => 65.00, 'alte_artikelnummer' => 'PK-PMOD', 'stueckzahl' => 5, 'kommentar' => 'Gelenkartikuliert'],

            // ── Kabelmanagement ────────────────────────────────────────────────
            ['bezeichnung' => 'NYM-J 3×1,5mm² Ring 50m', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Lapp GmbH', 'ek_ohne_st' => 38.00, 'vk_ohne_st' => 65.00, 'alte_artikelnummer' => 'LP-NYM315', 'stueckzahl' => 10, 'kommentar' => 'Installationskabel grau'],
            ['bezeichnung' => 'NYM-J 5×2,5mm² Ring 50m', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Lapp GmbH', 'ek_ohne_st' => 72.00, 'vk_ohne_st' => 120.00, 'alte_artikelnummer' => 'LP-NYM525', 'stueckzahl' => 5, 'kommentar' => 'Installationskabel grau'],
            ['bezeichnung' => 'Kabelkanal 60×40mm weiß 2m', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Hager SE', 'ek_ohne_st' => 4.20, 'vk_ohne_st' => 8.50, 'alte_artikelnummer' => 'HG-KK6040', 'stueckzahl' => 40, 'kommentar' => 'Kunststoff mit Deckel'],
            ['bezeichnung' => 'Wellrohr M20 PA grau 10m', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Pflitsch GmbH', 'ek_ohne_st' => 8.50, 'vk_ohne_st' => 15.90, 'alte_artikelnummer' => 'PF-WR-M20', 'stueckzahl' => 15, 'kommentar' => 'IP66 zugelassen'],
            ['bezeichnung' => 'Sicherungsautomat B16A 1-pol.', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Hager SE', 'ek_ohne_st' => 5.90, 'vk_ohne_st' => 11.50, 'alte_artikelnummer' => 'HG-SA-B16', 'stueckzahl' => 20, 'kommentar' => 'DIN-Schiene'],
            ['bezeichnung' => 'Sicherungsautomat B25A 3-pol.', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Hager SE', 'ek_ohne_st' => 18.00, 'vk_ohne_st' => 32.00, 'alte_artikelnummer' => 'HG-SA-B25-3', 'stueckzahl' => 10, 'kommentar' => 'DIN-Schiene'],
            ['bezeichnung' => 'Feuchtraum-Verteiler IP65 8-fach', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Hager SE', 'ek_ohne_st' => 38.00, 'vk_ohne_st' => 64.00, 'alte_artikelnummer' => 'HG-FV8', 'stueckzahl' => 4, 'kommentar' => 'AP-Montage'],
            ['bezeichnung' => 'Kabelschuh 10mm² Ring M8', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Cimco GmbH', 'ek_ohne_st' => 0.35, 'vk_ohne_st' => 0.90, 'alte_artikelnummer' => 'CM-KS10M8', 'stueckzahl' => 200, 'kommentar' => 'Cu verzinnt'],
            ['bezeichnung' => 'Kabelmarkierer Leiterband 1-10 (10×10St.)', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Hellermann Tyton', 'ek_ohne_st' => 4.80, 'vk_ohne_st' => 9.90, 'alte_artikelnummer' => 'HT-KM-110', 'stueckzahl' => 20, 'kommentar' => 'Selbstklebend'],
            ['bezeichnung' => 'Lüsterklemme 2,5mm² 12-fach', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Wago GmbH', 'ek_ohne_st' => 1.20, 'vk_ohne_st' => 2.50, 'alte_artikelnummer' => 'WG-LK25-12', 'stueckzahl' => 60, 'kommentar' => ''],
            ['bezeichnung' => 'WAGO 221 Klemme 5-pol. 2,5mm²', 'geraet' => 'Kabelmanagement', 'lieferant' => 'Wago GmbH', 'ek_ohne_st' => 0.65, 'vk_ohne_st' => 1.40, 'alte_artikelnummer' => 'WG-221-5', 'stueckzahl' => 200, 'kommentar' => 'Hebel-Klemme universal'],

            // ── Pneumatik ──────────────────────────────────────────────────────
            ['bezeichnung' => 'Druckluftschlauch 10mm Ø 20m', 'geraet' => 'Pneumatik', 'lieferant' => 'Festo AG', 'ek_ohne_st' => 24.00, 'vk_ohne_st' => 42.00, 'alte_artikelnummer' => 'FS-PAN10-20', 'stueckzahl' => 6, 'kommentar' => 'max. 10 bar'],
            ['bezeichnung' => 'Druckluftschlauch 6mm Ø 10m', 'geraet' => 'Pneumatik', 'lieferant' => 'Festo AG', 'ek_ohne_st' => 12.00, 'vk_ohne_st' => 22.00, 'alte_artikelnummer' => 'FS-PAN6-10', 'stueckzahl' => 8, 'kommentar' => 'Polyurethan, flexibel'],
            ['bezeichnung' => 'Druckluftkupplung NW 7.2 Stecker', 'geraet' => 'Pneumatik', 'lieferant' => 'Rectus GmbH', 'ek_ohne_st' => 2.80, 'vk_ohne_st' => 5.90, 'alte_artikelnummer' => 'RC-NW72-ST', 'stueckzahl' => 30, 'kommentar' => 'Stahlgehäuse'],
            ['bezeichnung' => 'Druckluftkupplung NW 7.2 Muffe', 'geraet' => 'Pneumatik', 'lieferant' => 'Rectus GmbH', 'ek_ohne_st' => 3.20, 'vk_ohne_st' => 6.50, 'alte_artikelnummer' => 'RC-NW72-MF', 'stueckzahl' => 20, 'kommentar' => 'Absperrventil integriert'],
            ['bezeichnung' => 'Luftpistole Blaspistole', 'geraet' => 'Pneumatik', 'lieferant' => 'Festo AG', 'ek_ohne_st' => 9.50, 'vk_ohne_st' => 17.90, 'alte_artikelnummer' => 'FS-BLP', 'stueckzahl' => 8, 'kommentar' => 'OSHA-konform mit Düse'],
            ['bezeichnung' => 'Druckregler + Manometer 1/4"', 'geraet' => 'Pneumatik', 'lieferant' => 'Festo AG', 'ek_ohne_st' => 18.00, 'vk_ohne_st' => 32.00, 'alte_artikelnummer' => 'FS-DRM14', 'stueckzahl' => 4, 'kommentar' => '0–10 bar einstellbar'],
            ['bezeichnung' => 'Nagler Druckluft-Magazintacker', 'geraet' => 'Pneumatik', 'lieferant' => 'Senco GmbH', 'ek_ohne_st' => 72.00, 'vk_ohne_st' => 119.00, 'alte_artikelnummer' => 'SN-MT-65', 'stueckzahl' => 2, 'kommentar' => '15–65mm Klammern'],
            ['bezeichnung' => 'Druckluft-Ratschenschlüssel 1/2"', 'geraet' => 'Pneumatik', 'lieferant' => 'Hazet GmbH', 'ek_ohne_st' => 95.00, 'vk_ohne_st' => 155.00, 'alte_artikelnummer' => 'HZ-DLRS12', 'stueckzahl' => 2, 'kommentar' => '68Nm max.'],

            // ── Messtechnik ────────────────────────────────────────────────────
            ['bezeichnung' => 'Digitales Multimeter Fluke 115', 'geraet' => 'Messtechnik', 'lieferant' => 'Fluke Deutschland GmbH', 'ek_ohne_st' => 89.00, 'vk_ohne_st' => 139.00, 'alte_artikelnummer' => 'FL-115', 'stueckzahl' => 4, 'kommentar' => 'CAT III 600V, VDE'],
            ['bezeichnung' => 'Stromzange Fluke 323', 'geraet' => 'Messtechnik', 'lieferant' => 'Fluke Deutschland GmbH', 'ek_ohne_st' => 75.00, 'vk_ohne_st' => 120.00, 'alte_artikelnummer' => 'FL-323', 'stueckzahl' => 3, 'kommentar' => 'AC bis 400A, CAT III'],
            ['bezeichnung' => 'Laser-Entfernungsmesser Bosch GLM 50C', 'geraet' => 'Messtechnik', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 55.00, 'vk_ohne_st' => 89.00, 'alte_artikelnummer' => 'BP-GLM50C', 'stueckzahl' => 5, 'kommentar' => '0.05–50m, Bluetooth'],
            ['bezeichnung' => 'Infrarot-Thermometer Fluke 62 MAX', 'geraet' => 'Messtechnik', 'lieferant' => 'Fluke Deutschland GmbH', 'ek_ohne_st' => 65.00, 'vk_ohne_st' => 105.00, 'alte_artikelnummer' => 'FL-62MAX', 'stueckzahl' => 3, 'kommentar' => '-30°C bis +500°C'],
            ['bezeichnung' => 'Drehmomentschlüssel 1/2" 40–200Nm', 'geraet' => 'Messtechnik', 'lieferant' => 'Gedore GmbH', 'ek_ohne_st' => 55.00, 'vk_ohne_st' => 89.00, 'alte_artikelnummer' => 'GD-DMS200', 'stueckzahl' => 3, 'kommentar' => 'DIN ISO 6789'],
            ['bezeichnung' => 'Bügelmessschraube 0–25mm', 'geraet' => 'Messtechnik', 'lieferant' => 'Mitutoyo GmbH', 'ek_ohne_st' => 28.00, 'vk_ohne_st' => 48.00, 'alte_artikelnummer' => 'MT-BM025', 'stueckzahl' => 3, 'kommentar' => '0.001mm Auflösung'],
            ['bezeichnung' => 'Digitale Schieblehre 150mm', 'geraet' => 'Messtechnik', 'lieferant' => 'Mitutoyo GmbH', 'ek_ohne_st' => 22.00, 'vk_ohne_st' => 38.00, 'alte_artikelnummer' => 'MT-DSL150', 'stueckzahl' => 5, 'kommentar' => '0.01mm, Edelstahl'],
            ['bezeichnung' => 'Schallpegelmessgerät Testo 816-1', 'geraet' => 'Messtechnik', 'lieferant' => 'Testo SE', 'ek_ohne_st' => 89.00, 'vk_ohne_st' => 145.00, 'alte_artikelnummer' => 'TS-8161', 'stueckzahl' => 1, 'kommentar' => '30–130 dB'],
            ['bezeichnung' => 'Feuchtemessgerät Testo 606-1', 'geraet' => 'Messtechnik', 'lieferant' => 'Testo SE', 'ek_ohne_st' => 42.00, 'vk_ohne_st' => 72.00, 'alte_artikelnummer' => 'TS-6061', 'stueckzahl' => 2, 'kommentar' => 'Einschuss, Holz & Baustoffe'],

            // ── Schmiermittel / Chemie ─────────────────────────────────────────
            ['bezeichnung' => 'WD-40 Mehrzweck 400ml Spray', 'geraet' => 'Schmiermittel', 'lieferant' => 'WD-40 Company', 'ek_ohne_st' => 3.90, 'vk_ohne_st' => 7.50, 'alte_artikelnummer' => 'WD-MZ400', 'stueckzahl' => 30, 'kommentar' => 'Rostlöser & Schmiermittel'],
            ['bezeichnung' => 'Kupferpaste 250g', 'geraet' => 'Schmiermittel', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 8.50, 'vk_ohne_st' => 15.90, 'alte_artikelnummer' => 'W-KUP250', 'stueckzahl' => 10, 'kommentar' => 'bis 1100°C hitzebeständig'],
            ['bezeichnung' => 'Motoröl 5W-30 5L', 'geraet' => 'Schmiermittel', 'lieferant' => 'Castrol GmbH', 'ek_ohne_st' => 22.00, 'vk_ohne_st' => 38.00, 'alte_artikelnummer' => 'CA-5W30-5L', 'stueckzahl' => 8, 'kommentar' => 'Vollsynthetisch, ACEA A5'],
            ['bezeichnung' => 'Hydrauliköl HLP 46 5L', 'geraet' => 'Schmiermittel', 'lieferant' => 'Castrol GmbH', 'ek_ohne_st' => 18.00, 'vk_ohne_st' => 31.00, 'alte_artikelnummer' => 'CA-HLP46-5L', 'stueckzahl' => 6, 'kommentar' => 'DIN 51524 Teil 2'],
            ['bezeichnung' => 'Federschmierstoff Lithiumfett 400g', 'geraet' => 'Schmiermittel', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 6.90, 'vk_ohne_st' => 12.50, 'alte_artikelnummer' => 'W-LIF400', 'stueckzahl' => 15, 'kommentar' => 'NLGI 2, Kartusche'],
            ['bezeichnung' => 'Kettenspray 500ml', 'geraet' => 'Schmiermittel', 'lieferant' => 'Liqui Moly GmbH', 'ek_ohne_st' => 5.50, 'vk_ohne_st' => 10.50, 'alte_artikelnummer' => 'LM-KTS500', 'stueckzahl' => 12, 'kommentar' => 'Weißes Haftfett'],
            ['bezeichnung' => 'Trennmittel / Formtrennspray 400ml', 'geraet' => 'Schmiermittel', 'lieferant' => 'Zeller+Gmelin GmbH', 'ek_ohne_st' => 4.80, 'vk_ohne_st' => 9.20, 'alte_artikelnummer' => 'ZG-TMS400', 'stueckzahl' => 15, 'kommentar' => 'Silikonfrei'],
            ['bezeichnung' => 'Metallpflege Autosol 75ml', 'geraet' => 'Schmiermittel', 'lieferant' => 'Autosol GmbH', 'ek_ohne_st' => 4.20, 'vk_ohne_st' => 7.90, 'alte_artikelnummer' => 'AS-MP75', 'stueckzahl' => 20, 'kommentar' => 'Politur für Chrom/Messing'],

            // ── Büromaterial / Lager ───────────────────────────────────────────
            ['bezeichnung' => 'Klebeband Lageretiketten 100×150mm', 'geraet' => 'Lagerzubehör', 'lieferant' => 'Avery Zweckform GmbH', 'ek_ohne_st' => 12.00, 'vk_ohne_st' => 21.00, 'alte_artikelnummer' => 'AZ-LE150', 'stueckzahl' => 10, 'kommentar' => 'VPE 100 Etiketten'],
            ['bezeichnung' => 'Palettenfolie Stretchfolie 50cm', 'geraet' => 'Lagerzubehör', 'lieferant' => 'Tesa SE', 'ek_ohne_st' => 8.90, 'vk_ohne_st' => 16.90, 'alte_artikelnummer' => 'TS-PF50', 'stueckzahl' => 20, 'kommentar' => '300m, 23my'],
            ['bezeichnung' => 'Lagerkiste PP 600×400×220mm', 'geraet' => 'Lagerzubehör', 'lieferant' => 'Rako Werkzeuge GmbH', 'ek_ohne_st' => 9.50, 'vk_ohne_st' => 17.90, 'alte_artikelnummer' => 'RK-PK-640', 'stueckzahl' => 30, 'kommentar' => 'stapelbar, grau'],
            ['bezeichnung' => 'Regalsystem Metall 200×100×50cm', 'geraet' => 'Lagerzubehör', 'lieferant' => 'Stumpf GmbH', 'ek_ohne_st' => 85.00, 'vk_ohne_st' => 139.00, 'alte_artikelnummer' => 'SFT-RS-200', 'stueckzahl' => 6, 'kommentar' => '5 Böden, 175kg/Boden'],
            ['bezeichnung' => 'Hubwagen 2500kg', 'geraet' => 'Lagerzubehör', 'lieferant' => 'Bishamon GmbH', 'ek_ohne_st' => 180.00, 'vk_ohne_st' => 289.00, 'alte_artikelnummer' => 'BI-HW2500', 'stueckzahl' => 2, 'kommentar' => 'Gabellänge 1150mm'],
            ['bezeichnung' => 'Stapelpalette Holz 1200×800mm', 'geraet' => 'Lagerzubehör', 'lieferant' => 'EPAL', 'ek_ohne_st' => 8.00, 'vk_ohne_st' => 14.50, 'alte_artikelnummer' => 'EP-1280', 'stueckzahl' => 50, 'kommentar' => 'EUR-Norm, unbehandelt'],

            // ── Befestigungstechnik ────────────────────────────────────────────
            ['bezeichnung' => 'Gewindestange M10 DIN 976 1m', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 3.20, 'vk_ohne_st' => 6.90, 'alte_artikelnummer' => 'W-GS-M10', 'stueckzahl' => 50, 'kommentar' => 'Stahl 4.8 verzinkt'],
            ['bezeichnung' => 'Ankerbolzen M10×80 FH II Hilti', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Hilti Deutschland AG', 'ek_ohne_st' => 2.80, 'vk_ohne_st' => 5.50, 'alte_artikelnummer' => 'HI-FH2-M10', 'stueckzahl' => 60, 'kommentar' => 'Innengewindeanker'],
            ['bezeichnung' => 'Rahmendübel SX 10×60', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Fischer GmbH', 'ek_ohne_st' => 0.45, 'vk_ohne_st' => 1.00, 'alte_artikelnummer' => 'FI-SX1060', 'stueckzahl' => 400, 'kommentar' => ''],
            ['bezeichnung' => 'Verbundanker HIT-RE 500 V4 500ml', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Hilti Deutschland AG', 'ek_ohne_st' => 35.00, 'vk_ohne_st' => 59.00, 'alte_artikelnummer' => 'HI-HITRE500', 'stueckzahl' => 8, 'kommentar' => '2K-Mörtel, ETA zugelassen'],
            ['bezeichnung' => 'Ringschraube M12 Gr. 50 DIN 580', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Würth GmbH', 'ek_ohne_st' => 2.10, 'vk_ohne_st' => 4.50, 'alte_artikelnummer' => 'W-RS-M12', 'stueckzahl' => 40, 'kommentar' => ''],
            ['bezeichnung' => 'Nieten Blindnieten 4×10 Alu (200 Stk.)', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Marson / ITW', 'ek_ohne_st' => 4.50, 'vk_ohne_st' => 8.90, 'alte_artikelnummer' => 'IT-BN410A', 'stueckzahl' => 20, 'kommentar' => 'VPE 200 Stk.'],
            ['bezeichnung' => 'Clipsverbinder 10mm Rohr', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Festo AG', 'ek_ohne_st' => 0.80, 'vk_ohne_st' => 1.80, 'alte_artikelnummer' => 'FS-CV10', 'stueckzahl' => 100, 'kommentar' => 'Lösbar'],
            ['bezeichnung' => 'T-Nut-Verbinder 30×30 Alu-Profil', 'geraet' => 'Befestigungstechnik', 'lieferant' => 'Item Industrietechnik GmbH', 'ek_ohne_st' => 2.50, 'vk_ohne_st' => 5.20, 'alte_artikelnummer' => 'IT-TNV30', 'stueckzahl' => 80, 'kommentar' => 'Set mit M6 Bolzen'],

            // ── Reinigung & Entsorgung ─────────────────────────────────────────
            ['bezeichnung' => 'Industriekehrbesen 50cm', 'geraet' => 'Reinigung', 'lieferant' => 'Vileda Professional', 'ek_ohne_st' => 14.00, 'vk_ohne_st' => 24.00, 'alte_artikelnummer' => 'VP-KB50', 'stueckzahl' => 5, 'kommentar' => 'Naturhaar, ohne Stiel'],
            ['bezeichnung' => 'Besen-Teleskopstiel 130–200cm', 'geraet' => 'Reinigung', 'lieferant' => 'Vileda Professional', 'ek_ohne_st' => 12.00, 'vk_ohne_st' => 21.00, 'alte_artikelnummer' => 'VP-TS200', 'stueckzahl' => 5, 'kommentar' => 'Alu, passend für KB50'],
            ['bezeichnung' => 'Schmutzwasser-Tauchpumpe 12000 l/h', 'geraet' => 'Reinigung', 'lieferant' => 'Schmutz-Pump GmbH', 'ek_ohne_st' => 68.00, 'vk_ohne_st' => 109.00, 'alte_artikelnummer' => 'SP-SWP12', 'stueckzahl' => 2, 'kommentar' => '0.75kW, Ø 35mm Partikel'],
            ['bezeichnung' => 'Öl-Absorbtionsmittel 20kg Sack', 'geraet' => 'Reinigung', 'lieferant' => 'Lunasorb GmbH', 'ek_ohne_st' => 12.00, 'vk_ohne_st' => 21.00, 'alte_artikelnummer' => 'LS-OAM20', 'stueckzahl' => 10, 'kommentar' => 'Granulat Klasse II'],
            ['bezeichnung' => 'Müllsäcke 120L schwarz 25er Rolle', 'geraet' => 'Reinigung', 'lieferant' => 'Papernet Spa', 'ek_ohne_st' => 5.50, 'vk_ohne_st' => 10.00, 'alte_artikelnummer' => 'PN-MS120', 'stueckzahl' => 20, 'kommentar' => '60my stark'],

            // ── Prüf- & Sicherheitstechnik ─────────────────────────────────────
            ['bezeichnung' => 'Prüftafel DGUV V3 Din A5', 'geraet' => 'Prüftechnik', 'lieferant' => 'Avery Zweckform GmbH', 'ek_ohne_st' => 0.25, 'vk_ohne_st' => 0.80, 'alte_artikelnummer' => 'AZ-PT-A5', 'stueckzahl' => 200, 'kommentar' => 'Prüfnachweis-Etiketten'],
            ['bezeichnung' => 'Absperrband rot/weiß 80mm×500m', 'geraet' => 'Prüftechnik', 'lieferant' => 'Tesa SE', 'ek_ohne_st' => 8.50, 'vk_ohne_st' => 15.50, 'alte_artikelnummer' => 'TS-ABB500', 'stueckzahl' => 5, 'kommentar' => 'Flatterband PE'],
            ['bezeichnung' => 'Feuerlöscher ABC 6kg', 'geraet' => 'Prüftechnik', 'lieferant' => 'Minimax GmbH', 'ek_ohne_st' => 48.00, 'vk_ohne_st' => 79.00, 'alte_artikelnummer' => 'MX-FL6', 'stueckzahl' => 8, 'kommentar' => 'PG 25, Prüfung alle 2 Jahre'],
            ['bezeichnung' => 'Verbandkasten DIN 13169 Betrieb', 'geraet' => 'Prüftechnik', 'lieferant' => 'Söhngen GmbH', 'ek_ohne_st' => 22.00, 'vk_ohne_st' => 38.00, 'alte_artikelnummer' => 'SN-VK13169', 'stueckzahl' => 4, 'kommentar' => 'Wandhalterung inkl.'],
            ['bezeichnung' => 'Augenspülstation 500ml', 'geraet' => 'Prüftechnik', 'lieferant' => 'Söhngen GmbH', 'ek_ohne_st' => 12.00, 'vk_ohne_st' => 21.00, 'alte_artikelnummer' => 'SN-ASP500', 'stueckzahl' => 6, 'kommentar' => 'Isotonische Kochsalzlösung'],
            ['bezeichnung' => 'Sicherheitsvorhängeschloss Rot', 'geraet' => 'Prüftechnik', 'lieferant' => 'Brady GmbH', 'ek_ohne_st' => 6.80, 'vk_ohne_st' => 12.90, 'alte_artikelnummer' => 'BR-SVL-R', 'stueckzahl' => 15, 'kommentar' => 'LOTO, gleichschließend'],

            // ── IT / Elektronik ────────────────────────────────────────────────
            ['bezeichnung' => 'Netzwerkkabel CAT6 Patch 2m', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Delock GmbH', 'ek_ohne_st' => 1.80, 'vk_ohne_st' => 3.90, 'alte_artikelnummer' => 'DL-CAT6-2', 'stueckzahl' => 30, 'kommentar' => 'RJ45, gelb'],
            ['bezeichnung' => 'USB-C Ladekabel 1m 65W', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Anker Technology', 'ek_ohne_st' => 8.50, 'vk_ohne_st' => 15.90, 'alte_artikelnummer' => 'AK-UCC1', 'stueckzahl' => 10, 'kommentar' => 'PD kompatibel'],
            ['bezeichnung' => 'HDMI-Kabel 2.0 3m', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Delock GmbH', 'ek_ohne_st' => 5.50, 'vk_ohne_st' => 10.90, 'alte_artikelnummer' => 'DL-HDMI2-3', 'stueckzahl' => 8, 'kommentar' => '4K@60Hz'],
            ['bezeichnung' => 'Verlängerungskabel Schuko 5m', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Brennenstuhl GmbH', 'ek_ohne_st' => 7.50, 'vk_ohne_st' => 13.90, 'alte_artikelnummer' => 'BR-VL5M', 'stueckzahl' => 12, 'kommentar' => 'H05VV-F 3G1.5'],
            ['bezeichnung' => 'Akkupack 18V 4Ah Li-Ion Makita', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Makita GmbH', 'ek_ohne_st' => 55.00, 'vk_ohne_st' => 89.00, 'alte_artikelnummer' => 'MK-BL1840B', 'stueckzahl' => 8, 'kommentar' => 'BL1840B, LXT Serie'],
            ['bezeichnung' => 'Akkupack 18V 5Ah Li-Ion Bosch', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Bosch Professional', 'ek_ohne_st' => 65.00, 'vk_ohne_st' => 105.00, 'alte_artikelnummer' => 'BP-GBA18V50', 'stueckzahl' => 8, 'kommentar' => 'GBA 18V 5.0Ah'],
            ['bezeichnung' => 'Schnellladegerät 18V Makita DC18RD', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Makita GmbH', 'ek_ohne_st' => 62.00, 'vk_ohne_st' => 99.00, 'alte_artikelnummer' => 'MK-DC18RD', 'stueckzahl' => 4, 'kommentar' => 'Doppellader, 30 min.'],
            ['bezeichnung' => 'Barcode-Scanner USB Honeywell 1202g', 'geraet' => 'IT-Zubehör', 'lieferant' => 'Honeywell AG', 'ek_ohne_st' => 85.00, 'vk_ohne_st' => 139.00, 'alte_artikelnummer' => 'HW-1202G', 'stueckzahl' => 3, 'kommentar' => '1D, Plug & Play'],

            // ── Arbeitsmöbel / Einrichtung ─────────────────────────────────────
            ['bezeichnung' => 'Werkbank Stahl 1500×700mm', 'geraet' => 'Arbeitsmöbel', 'lieferant' => 'Lista GmbH', 'ek_ohne_st' => 420.00, 'vk_ohne_st' => 680.00, 'alte_artikelnummer' => 'LI-WB15', 'stueckzahl' => 3, 'kommentar' => '700kg Traglast, stahlartig'],
            ['bezeichnung' => 'Werkzeugschrank 5-Schubladen', 'geraet' => 'Arbeitsmöbel', 'lieferant' => 'Lista GmbH', 'ek_ohne_st' => 350.00, 'vk_ohne_st' => 560.00, 'alte_artikelnummer' => 'LI-WZS5', 'stueckzahl' => 2, 'kommentar' => 'abschließbar, 700×560mm'],
            ['bezeichnung' => 'Lochwand-Halterungssystem 120×60cm', 'geraet' => 'Arbeitsmöbel', 'lieferant' => 'Raco Tools', 'ek_ohne_st' => 28.00, 'vk_ohne_st' => 49.00, 'alte_artikelnummer' => 'RC-LW1260', 'stueckzahl' => 6, 'kommentar' => 'Stahl lackiert, inkl. Haken-Set'],
            ['bezeichnung' => 'Hocker klappbar Industriestandard', 'geraet' => 'Arbeitsmöbel', 'lieferant' => 'Haillo GmbH', 'ek_ohne_st' => 18.00, 'vk_ohne_st' => 32.00, 'alte_artikelnummer' => 'HL-HKI', 'stueckzahl' => 8, 'kommentar' => '150kg, Höhe 65cm'],
            ['bezeichnung' => 'Arbeitsstuhl ESD Bimos 9154', 'geraet' => 'Arbeitsmöbel', 'lieferant' => 'Bimos GmbH', 'ek_ohne_st' => 185.00, 'vk_ohne_st' => 299.00, 'alte_artikelnummer' => 'BI-9154', 'stueckzahl' => 3, 'kommentar' => 'Antistatisch, höhenverstellbar'],

            // ── Schweißtechnik ─────────────────────────────────────────────────
            ['bezeichnung' => 'Schweißdraht SG2 0.8mm 5kg Spule', 'geraet' => 'Schweißtechnik', 'lieferant' => 'EWM AG', 'ek_ohne_st' => 18.00, 'vk_ohne_st' => 31.00, 'alte_artikelnummer' => 'EW-SD08-5', 'stueckzahl' => 15, 'kommentar' => 'MAG-Schweißen'],
            ['bezeichnung' => 'Schweißschutzgas CO2 10L Stahlflasche', 'geraet' => 'Schweißtechnik', 'lieferant' => 'Linde Gas GmbH', 'ek_ohne_st' => 32.00, 'vk_ohne_st' => 55.00, 'alte_artikelnummer' => 'LN-CO2-10', 'stueckzahl' => 4, 'kommentar' => 'inkl. Pfand'],
            ['bezeichnung' => 'Schweißerhandschuh Rindspaltleder', 'geraet' => 'Schweißtechnik', 'lieferant' => 'Jutec GmbH', 'ek_ohne_st' => 4.50, 'vk_ohne_st' => 8.90, 'alte_artikelnummer' => 'JT-SHG-RL', 'stueckzahl' => 20, 'kommentar' => 'Gr. L, CE Kat. II'],
            ['bezeichnung' => 'Schweißhelm Optrel e680', 'geraet' => 'Schweißtechnik', 'lieferant' => 'Optrel AG', 'ek_ohne_st' => 165.00, 'vk_ohne_st' => 259.00, 'alte_artikelnummer' => 'OP-E680', 'stueckzahl' => 2, 'kommentar' => 'Automatik-Verdunkelung DIN 5–13'],
            ['bezeichnung' => 'Elektroden E6013 2.5mm 1kg', 'geraet' => 'Schweißtechnik', 'lieferant' => 'EWM AG', 'ek_ohne_st' => 5.50, 'vk_ohne_st' => 10.00, 'alte_artikelnummer' => 'EW-E6013-25', 'stueckzahl' => 20, 'kommentar' => 'Rutil-Elektrode'],

            // ── Sonstiges ──────────────────────────────────────────────────────
            ['bezeichnung' => 'Transportwagen 3-Etagen 90×45cm', 'geraet' => 'Sonstiges', 'lieferant' => 'Kongamek GmbH', 'ek_ohne_st' => 65.00, 'vk_ohne_st' => 105.00, 'alte_artikelnummer' => 'KM-TW3E', 'stueckzahl' => 4, 'kommentar' => '300kg Traglast, Gummiräder'],
            ['bezeichnung' => 'Flachbett-Transportwagen 100×50cm', 'geraet' => 'Sonstiges', 'lieferant' => 'Kongamek GmbH', 'ek_ohne_st' => 55.00, 'vk_ohne_st' => 89.00, 'alte_artikelnummer' => 'KM-FBW100', 'stueckzahl' => 3, 'kommentar' => '200kg, Flachbett'],
            ['bezeichnung' => 'Trittleiter 3-stufig Alu', 'geraet' => 'Sonstiges', 'lieferant' => 'Hailo GmbH', 'ek_ohne_st' => 42.00, 'vk_ohne_st' => 69.00, 'alte_artikelnummer' => 'HA-TL3', 'stueckzahl' => 4, 'kommentar' => 'EN 131, max 150kg'],
            ['bezeichnung' => 'Mehrzweckleiter 3×8 Sprossen', 'geraet' => 'Sonstiges', 'lieferant' => 'Hailo GmbH', 'ek_ohne_st' => 128.00, 'vk_ohne_st' => 199.00, 'alte_artikelnummer' => 'HA-MZL38', 'stueckzahl' => 2, 'kommentar' => 'Alu, 4-fach verwendbar'],
            ['bezeichnung' => 'Magnetheber 100kg runder Typ', 'geraet' => 'Sonstiges', 'lieferant' => 'Magswitch GmbH', 'ek_ohne_st' => 38.00, 'vk_ohne_st' => 65.00, 'alte_artikelnummer' => 'MS-MH100', 'stueckzahl' => 3, 'kommentar' => 'Schaltbarer Permanent-Magnet'],
            ['bezeichnung' => 'Spanngurt 4m 2500daN 2-tlg.', 'geraet' => 'Sonstiges', 'lieferant' => 'Dolezych GmbH', 'ek_ohne_st' => 5.50, 'vk_ohne_st' => 10.90, 'alte_artikelnummer' => 'DO-SG4M', 'stueckzahl' => 20, 'kommentar' => 'EN 12195-2'],
            ['bezeichnung' => 'Ratschengurt 6m 2500daN', 'geraet' => 'Sonstiges', 'lieferant' => 'Dolezych GmbH', 'ek_ohne_st' => 8.00, 'vk_ohne_st' => 14.90, 'alte_artikelnummer' => 'DO-RG6M', 'stueckzahl' => 16, 'kommentar' => 'EN 12195-2'],
            ['bezeichnung' => 'Schäkel 2t Typ BL oval', 'geraet' => 'Sonstiges', 'lieferant' => 'Thiele GmbH', 'ek_ohne_st' => 4.20, 'vk_ohne_st' => 8.20, 'alte_artikelnummer' => 'TH-BL2T', 'stueckzahl' => 20, 'kommentar' => 'DIN 82101 Form B'],
            ['bezeichnung' => 'Seilschloss 8mm 30m Stahl verzinkt', 'geraet' => 'Sonstiges', 'lieferant' => 'Thiele GmbH', 'ek_ohne_st' => 32.00, 'vk_ohne_st' => 55.00, 'alte_artikelnummer' => 'TH-SS8-30', 'stueckzahl' => 2, 'kommentar' => 'SE8, 6×19 Litzen'],
            ['bezeichnung' => 'Reifendruckprüfer Michelin Digit', 'geraet' => 'Sonstiges', 'lieferant' => 'Michelin GmbH', 'ek_ohne_st' => 12.00, 'vk_ohne_st' => 21.00, 'alte_artikelnummer' => 'MC-DPDIG', 'stueckzahl' => 3, 'kommentar' => '0.1–6.5 bar digital'],
            ['bezeichnung' => 'Trichter 1L Metall mit Sieb', 'geraet' => 'Sonstiges', 'lieferant' => 'Pressol GmbH', 'ek_ohne_st' => 6.50, 'vk_ohne_st' => 12.00, 'alte_artikelnummer' => 'PS-TR1L', 'stueckzahl' => 4, 'kommentar' => 'Ölbeständig'],
            ['bezeichnung' => 'Öl-Kanne 1L Geradauslauf', 'geraet' => 'Sonstiges', 'lieferant' => 'Pressol GmbH', 'ek_ohne_st' => 4.80, 'vk_ohne_st' => 9.20, 'alte_artikelnummer' => 'PS-OK1L', 'stueckzahl' => 6, 'kommentar' => 'Metall, Edelstahltülle'],
        ];

        foreach ($items as $item) {
            if (!SckWarehouseItem::where('bezeichnung', $item['bezeichnung'])->exists()) {
                SckWarehouseItem::create($item);
            }
        }
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DatevInvoiceParserService;

class DatevInvoiceParserServiceTest extends TestCase
{
    public function test_extracts_stacked_multi_line_layout_items()
    {
        $service = new DatevInvoiceParserService();
        $text = <<<TEXT
SERVICE-CENTER KLEIN
Techn. Kundendienst für Kaffeevollautomaten & Friseurtechnik • Verkauf Friseurtechnik
ANDREAS KLEIN • ELEKTROMEISTER • Wiesenhof 1 • 36088 Hünfeld • Telefon (0 66 52) 7 93 58 20
Firma Klein, Andreas, Wiesenhof 1, 36088 Hünfeld
Friseursalon
Dirk Specht "Hair by Specht"
Kirchstr. 2
63549 Ronneburg
Rechnungs-Nr.: ausstehend
Kundennummer: 32118
Belegdatum: 16.07.2026
Liefer-/Leistungsdatum: 16.07.2026
Seite 1 von 1
Rechnung
Bezeichnung
Kaffee Allegretto Artigiano
Artikelnummer Menge Einheit
94673
1 500 g
Beutel
Preis
10,28 7,00 %
USt Betrag EUR
10,28
Summe:
7.00 % USt. auf EUR 10,28:
Endbetrag:
10,28
10.02
TEXT;

        $reflector = new \ReflectionClass($service);
        $method = $reflector->getMethod('extractLineItems');
        $method->setAccessible(true);
        
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($l) => $l !== ''
        ));
        
        $logs = [];
        $args = [$text, $lines, &$logs];
        $items = $method->invokeArgs($service, $args);

        $this->assertCount(1, $items);
        $this->assertEquals('Kaffee Allegretto Artigiano', $items[0]['bezeichnung']);
        $this->assertEquals('94673', $items[0]['artikelnummer']);
        $this->assertEquals(1.0, $items[0]['menge']);
        $this->assertEquals('Beutel', $items[0]['einheit']);
        $this->assertEquals(10.28, $items[0]['preis']);
        $this->assertEquals(7.0, $items[0]['ust']);
        $this->assertEquals(10.28, $items[0]['betrag']);
    }
}

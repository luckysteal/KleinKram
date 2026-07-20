<?php

namespace App\Services;

use App\Models\Sck\SckWarehouseItem;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class DatevInvoiceParserService
{
    /**
     * Parse an uploaded DATEV invoice PDF file and analyze items against the warehouse inventory.
     *
     * @param string $filePath Absolute or relative path to the PDF file
     * @return array
     */
    public function parsePdf(string $filePath): array
    {
        $debugLogs = [];
        $fileName = basename($filePath);
        $debugLogs[] = "[PARSER START] Beginning PDF parsing for file: {$fileName}";

        $text = $this->extractPdfText($filePath, $debugLogs);

        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($l) => $l !== ''
        ));

        $textLength = strlen($text);
        $lineCount = count($lines);
        $debugLogs[] = "[PDF EXTRACT COMPLETE] Total extracted text length: {$textLength} chars, {$lineCount} non-empty lines.";

        Log::info("DatevInvoiceParserService: Parsing PDF {$fileName}", [
            'raw_text_length' => $textLength,
            'line_count' => $lineCount,
        ]);

        $invoiceInfo = $this->extractInvoiceMetaData($text, $debugLogs);
        $rawItems = $this->extractLineItems($text, $lines, $debugLogs);

        $parsedItems = [];
        $allWarehouseItems = SckWarehouseItem::all();
        $debugLogs[] = "[CATALOG] Loaded " . $allWarehouseItems->count() . " warehouse items from database for catalog matching.";

        $hasUnmatched = false;
        $hasFuzzy = false;
        $hasPriceDiscrepancy = false;

        foreach ($rawItems as $idx => $raw) {
            $analysis = $this->analyzeItem($raw, $allWarehouseItems, $debugLogs, $idx + 1);

            if ($analysis['status'] === 'not_found') {
                $hasUnmatched = true;
            }
            if ($analysis['status'] === 'fuzzy_match') {
                $hasFuzzy = true;
            }
            if (!$analysis['price_match'] && $analysis['matched_item']) {
                $hasPriceDiscrepancy = true;
            }

            $parsedItems[] = $analysis;
        }

        $debugLogs[] = "[PARSER COMPLETE] Extracted " . count($parsedItems) . " item(s). Unmatched: " . ($hasUnmatched ? 'Yes' : 'No') . ", Fuzzy: " . ($hasFuzzy ? 'Yes' : 'No');

        Log::info("DatevInvoiceParserService: Parsing completed for {$fileName}", [
            'items_found' => count($parsedItems),
            'invoice_info' => $invoiceInfo,
            'debug_logs' => $debugLogs,
        ]);

        return [
            'success' => true,
            'invoice_info' => $invoiceInfo,
            'items' => $parsedItems,
            'has_unmatched' => $hasUnmatched,
            'has_fuzzy' => $hasFuzzy,
            'has_price_discrepancy' => $hasPriceDiscrepancy,
            'debug' => [
                'raw_text' => $text,
                'line_count' => $lineCount,
                'raw_items_count' => count($rawItems),
                'logs' => $debugLogs,
            ],
        ];
    }

    /**
     * Extract raw text from PDF file using multi-tier extraction:
     * Tier 1: Smalot PdfParser (pages + Form XObjects)
     * Tier 2: Ghostscript CLI (`gs -sDEVICE=txtwrite`)
     * Tier 3: Tesseract OCR (Ghostscript image rendering + Tesseract)
     */
    protected function extractPdfText(string $filePath, array &$debugLogs): string
    {
        $text = '';

        // TIER 1: Smalot PdfParser with XObjects
        $debugLogs[] = "[TIER 1] Attempting Smalot PdfParser (Pages + Form XObjects)...";
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $textParts = [];

            foreach ($pdf->getPages() as $pIndex => $page) {
                $pText = $page->getText();
                if (!empty(trim($pText))) {
                    $textParts[] = $pText;
                }

                // Extract text from Form XObjects (frequent in DATEV PDF forms)
                try {
                    $xObjects = $page->getXObjects();
                    foreach ($xObjects as $xObj) {
                        $xText = $xObj->getText();
                        if (!empty(trim($xText))) {
                            $textParts[] = $xText;
                        }
                    }
                } catch (\Throwable $xe) {
                    // XObject read warning
                }
            }

            $tier1Text = implode("\n", $textParts);
            $tier1Text = preg_replace('/\x{00A0}/u', ' ', $tier1Text);
            $tier1Text = str_replace("\r\n", "\n", $tier1Text);

            if (strlen(trim($tier1Text)) > 30) {
                $text = $tier1Text;
                $debugLogs[] = "[TIER 1 SUCCESS] Smalot extracted " . strlen($text) . " characters.";
            } else {
                $debugLogs[] = "[TIER 1 WARNING] Smalot extracted only " . strlen(trim($tier1Text)) . " characters.";
            }
        } catch (\Throwable $e) {
            $debugLogs[] = "[TIER 1 ERROR] Smalot PdfParser failed: " . $e->getMessage();
        }

        // TIER 2: Ghostscript CLI (txtwrite)
        if (empty(trim($text)) || strlen(trim($text)) < 30) {
            $debugLogs[] = "[TIER 2] Tier 1 text empty or too short. Trying Ghostscript (txtwrite)...";
            $gsBin = $this->findBinary(['gs', '/usr/bin/gs', '/usr/local/bin/gs', '/opt/homebrew/bin/gs']);

            if (!empty($gsBin)) {
                $cmd = sprintf('%s -dNOPAUSE -sDEVICE=txtwrite -sOutputFile=- -q -dBATCH %s 2>/dev/null', escapeshellarg($gsBin), escapeshellarg($filePath));
                $gsOutput = shell_exec($cmd);
                if ($gsOutput && strlen(trim($gsOutput)) > 30) {
                    $text = $gsOutput;
                    $debugLogs[] = "[TIER 2 SUCCESS] Ghostscript extracted " . strlen($text) . " characters.";
                } else {
                    $debugLogs[] = "[TIER 2 FAILED] Ghostscript returned no text.";
                }
            } else {
                $debugLogs[] = "[TIER 2 SKIP] Ghostscript binary not found.";
            }
        }

        // TIER 3: Cloud OCR API (OCR.Space API - 0 CPU load fallback for GoDaddy/cPanel shared hosting)
        if (empty(trim($text)) || strlen(trim($text)) < 30) {
            $debugLogs[] = "[TIER 3] Tier 1 & 2 local text extraction returned empty text. Trying Cloud OCR API (OCR.Space)...";
            try {
                $apiKey = config('services.ocr_space.api_key') ?: env('OCR_SPACE_API_KEY', 'K89317752788957');
                
                $uploadFileName = str_ends_with(strtolower($filePath), '.pdf') 
                    ? basename($filePath) 
                    : (basename($filePath) . '.pdf');

                $response = \Illuminate\Support\Facades\Http::timeout(35)
                    ->attach('file', file_get_contents($filePath), $uploadFileName)
                    ->post('https://api.ocr.space/parse/image', [
                        'apikey' => $apiKey,
                        'language' => 'ger',
                        'filetype' => 'PDF',
                        'isOverlayRequired' => 'false',
                        'OCREngine' => '2',
                        'detectOrientation' => 'true',
                        'scale' => 'true',
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $parsedText = '';
                    if (!empty($json['ParsedResults'])) {
                        foreach ($json['ParsedResults'] as $res) {
                            $parsedText .= ($res['ParsedText'] ?? '') . "\n";
                        }
                    }

                    if (strlen(trim($parsedText)) > 30) {
                        $text = $parsedText;
                        $debugLogs[] = "[TIER 3 SUCCESS] Cloud OCR API (OCR.Space) extracted " . strlen($text) . " characters with 0 local CPU usage.";
                    } else {
                        $errorMessage = $json['ErrorMessage'][0] ?? ($json['ErrorDetails'] ?? 'No text recognized by Cloud OCR');
                        $debugLogs[] = "[TIER 3 FAILED] Cloud OCR API returned no text. Error: {$errorMessage}";
                    }
                } else {
                    $debugLogs[] = "[TIER 3 FAILED] Cloud OCR API HTTP request failed with status: " . $response->status();
                }
            } catch (\Throwable $cloudErr) {
                $debugLogs[] = "[TIER 3 ERROR] Cloud OCR API exception: " . $cloudErr->getMessage();
            }
        }

        // Clean error messages or watermark words from extracted text
        $text = preg_replace('/^(?:Error|Please|Failed|Warning).*$\n?/mi', '', $text);
        $text = preg_replace('/\bENTWURF\b/iu', '', $text);

        return $text;
    }

    /**
     * Extract metadata (Invoice number, date, customer number) from invoice text.
     */
    protected function extractInvoiceMetaData(string $text, array &$debugLogs): array
    {
        $info = [
            'number' => null,
            'date' => null,
            'customer_number' => null,
        ];

        if (preg_match('/(?:Rechnungs-?Nr\.?|Rechnungsnummer|Beleg-?Nr\.?|Invoice-?No\.?):\s*([A-Za-z0-9\-_äöüÄÖÜ]+)/i', $text, $m)) {
            $info['number'] = trim($m[1]);
        }
        if (preg_match('/(?:Belegdatum|Rechnungsdatum|Datum):\s*([0-9\.]+)/i', $text, $m)) {
            $info['date'] = trim($m[1]);
        }
        if (preg_match('/(?:Kundennummer|Kunden-?Nr\.?|Kdnr\.?):\s*([A-Za-z0-9\-_]+)/i', $text, $m)) {
            $info['customer_number'] = trim($m[1]);
        }

        $debugLogs[] = "[METADATA] Extracted -> Invoice No: " . ($info['number'] ?? 'Not found') . ", Date: " . ($info['date'] ?? 'Not found') . ", Customer No: " . ($info['customer_number'] ?? 'Not found');

        return $info;
    }

    /**
     * Extract line item rows from invoice text.
     */
    protected function extractLineItems(string $text, array $lines, array &$debugLogs): array
    {
        $items = [];

        // Clean error lines from input
        $cleanLines = array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($l) => !empty($l) && !preg_match('/^(?:Error|Please|Failed|Warning)/i', $l)
        ));

        // Strategy 1: Strict line-by-line regex scanning
        $debugLogs[] = "[STRATEGY 1] Trying line-by-line regex scan over " . count($cleanLines) . " lines...";

        $linePatterns = [
            // Standard DATEV line item: "Musterartikel-1 Artikel-111 2 Stück 39,95 19,00 % 79,90"
            // (Uses [^\r\n]+? so it never matches across line breaks or absorbs header lines!)
            '/^(?P<bezeichnung>[^\r\n]+?)\s+(?P<artnr>[A-Za-z0-9\-_]{2,20})\s+(?P<menge>\d+(?:[\s\.,]\d+)?)\s+(?P<einheit>[A-Za-z0-9\säöüÄÖÜ]+?)\s+(?P<preis>\d+[\.,]\d{2})\s*(?:€|EUR)?\s+(?P<ust>\d+(?:[\.,]\d+)?)\s*%\s+(?P<betrag>-?\d+[\.,]\d{2})$/iu',

            // DATEV line item with ArtNr first: "Artikel-111 Musterartikel-1 2 Stück 39,95 19,00 % 79,90"
            '/^(?P<artnr>[A-Za-z0-9\-_]{3,20})\s+(?P<bezeichnung>[^\r\n]+?)\s+(?P<menge>\d+(?:[\s\.,]\d+)?)\s+(?P<einheit>[A-Za-z0-9\säöüÄÖÜ]+?)\s+(?P<preis>\d+[\.,]\d{2})\s*(?:€|EUR)?\s+(?P<ust>\d+(?:[\.,]\d+)?)\s*%\s+(?P<betrag>-?\d+[\.,]\d{2})$/iu',

            // DATEV line item without USt %: "Kaffee Allegretto 94673 1 Stück 10,28 10,28"
            '/^(?P<bezeichnung>[^\r\n]+?)\s+(?P<artnr>[A-Za-z0-9\-_]{2,20})\s+(?P<menge>\d+(?:[\s\.,]\d+)?)\s+(?P<einheit>Stk\.?|Stiick|Stück|kg|Kilogramm|g|l|ml|m|Packung|Pck\.?|Beutel|Flasche|Dose|Ktn|Karton)?\s+(?P<preis>\d+[\.,]\d{2})\s*(?:€|EUR)?\s+(?P<betrag>-?\d+[\.,]\d{2})\s*(?:€|EUR)?$/iu',
        ];

        foreach ($cleanLines as $lIdx => $line) {
            foreach ($linePatterns as $pIdx => $pattern) {
                if (preg_match($pattern, $line, $m)) {
                    $item = $this->cleanRawMatch($m);
                    if (!empty($item['bezeichnung']) && strlen($item['bezeichnung']) > 1) {
                        // Ignore header or summary lines
                        if (preg_match('/^(?:Bezeichnung|Artikelnummer|Menge|Einheit|Preis|USt|Betrag|Summe|Zwischensumme|Endbetrag)$/i', $item['bezeichnung'])) {
                            continue;
                        }
                        $items[] = $item;
                        $debugLogs[] = "[STRATEGY 1 MATCH] Line " . ($lIdx + 1) . ": '{$item['bezeichnung']}' (ArtNr: '{$item['artikelnummer']}', Price: {$item['preis']} EUR)";
                        break; // Move to next line
                    }
                }
            }
        }

        if (!empty($items)) {
            $debugLogs[] = "[STRATEGY 1 SUCCESS] Line scan extracted " . count($items) . " line item(s).";
            return $items;
        }

        // Strategy 2: Line-by-line table scanning
        $debugLogs[] = "[STRATEGY 2] Trying line-by-line table header scan...";
        $inTable = false;

        foreach ($lines as $lineIdx => $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;

            // Skip explanatory text lines
            if (stripos($trimmed, 'Wenn Sie') === 0 || stripos($trimmed, 'Bitte beachten') === 0) {
                continue;
            }

            // Detect table header line
            if (!$inTable && preg_match('/(?:Bezeichnung|Artikelbezeichnung|Beschreibung|Artikel|Gegenstand)/i', $trimmed) 
                && preg_match('/(?:Artikel-?Nr|Art\.?-?Nr|Pos|Menge|Anzahl|Preis|Betrag|Netto)/i', $trimmed)) {
                $inTable = true;
                $debugLogs[] = "[STRATEGY 2 HEADER] Found table header at line " . ($lineIdx + 1) . ": '{$trimmed}'";
                continue;
            }

            // Stop table scan on summary or footer lines
            if ($inTable && preg_match('/(?:Summe|Zwischensumme|Endbetrag|Gesamtbetrag|MwSt|Umsatzsteuer|Zahlbar|Netto)/i', $trimmed)) {
                $debugLogs[] = "[STRATEGY 2 END] Reached table footer at line " . ($lineIdx + 1) . ": '{$trimmed}'";
                $inTable = false;
                continue;
            }

            if ($inTable) {
                // Try parsing line inside table
                if (preg_match('/^(.*?)\s+([A-Za-z0-9\-_]{2,20})\s+(\d+(?:[\s\.,]\d+)?)\s+([A-Za-z0-9\säöüÄÖÜ]*?)\s+(\d+[\.,]\d{2})\s*(?:€|EUR)?\s+(\d+(?:[\.,]\d+)?)\s*%\s+(-?\d+[\.,]\d{2})$/u', $trimmed, $m)) {
                    $items[] = [
                        'bezeichnung' => trim($m[1]),
                        'artikelnummer' => trim($m[2]),
                        'menge' => $this->parseGermanNumber($m[3]),
                        'einheit' => trim($m[4]) ?: 'Stück',
                        'preis' => $this->parseGermanNumber($m[5]),
                        'ust' => $this->parseGermanNumber($m[6]),
                        'betrag' => $this->parseGermanNumber($m[7]),
                    ];
                    $debugLogs[] = "[STRATEGY 2 MATCH] Line " . ($lineIdx + 1) . " parsed: " . trim($m[1]) . " (ArtNr: " . trim($m[2]) . ", Price: " . $m[5] . ")";
                } elseif (preg_match('/^(.*?)\s+(\d+(?:[\s\.,]\d+)?)\s+([A-Za-z0-9\säöüÄÖÜ]+?)\s+(\d+[\.,]\d{2})\s*(?:€|EUR)?\s+(-?\d+[\.,]\d{2})$/u', $trimmed, $m)) {
                    $items[] = [
                        'bezeichnung' => trim($m[1]),
                        'artikelnummer' => '',
                        'menge' => $this->parseGermanNumber($m[2]),
                        'einheit' => trim($m[3]),
                        'preis' => $this->parseGermanNumber($m[4]),
                        'ust' => 19.0,
                        'betrag' => $this->parseGermanNumber($m[5]),
                    ];
                    $debugLogs[] = "[STRATEGY 2 MATCH] Line " . ($lineIdx + 1) . " parsed: " . trim($m[1]) . " (Price: " . $m[4] . ")";
                }
            }
        }

        if (!empty($items)) {
            $debugLogs[] = "[STRATEGY 2 SUCCESS] Table scan extracted " . count($items) . " item(s).";
            return $items;
        }

        // Strategy 3: Fallback loose line scanning
        $debugLogs[] = "[STRATEGY 3] Trying fallback loose regex on all " . count($lines) . " lines...";
        foreach ($lines as $lineIdx => $line) {
            $trimmed = trim($line);

            // Skip header/footer lines and commentary lines
            if (preg_match('/(?:Summe|Zwischensumme|Endbetrag|Gesamtbetrag|Zahlbar|IBAN|BIC|Bank|Ust-ID|Steuer-Nr|Wenn Sie|Bitte beachten)/i', $trimmed)) {
                continue;
            }

            // Pattern: [Text with quantity/name] [Price] [Tax%] [Betrag]
            if (preg_match('/^(.*?)\s+(\d+[\.,]\d{2})\s*(?:€|EUR)?\s+(\d+(?:[\.,]\d+)?)\s*%\s+(-?\d+[\.,]\d{2})\s*(?:€|EUR)?$/u', $trimmed, $m)) {
                $nameAndQty = trim($m[1]);
                $artNr = '';
                $menge = 1;
                $einheit = 'Stück';

                // Check if name contains ArtNr
                if (preg_match('/([A-Za-z0-9\-_]{4,15})/', $nameAndQty, $artM)) {
                    $artNr = $artM[1];
                }
                
                // Check quantity
                if (preg_match('/(\d+)\s*(?:x|Stk\.?|Stück|Kilogramm|kg|Beutel)/i', $nameAndQty, $qtyM)) {
                    $menge = (float)$qtyM[1];
                }

                $items[] = [
                    'bezeichnung' => $nameAndQty,
                    'artikelnummer' => $artNr,
                    'menge' => $menge,
                    'einheit' => $einheit,
                    'preis' => $this->parseGermanNumber($m[2]),
                    'ust' => $this->parseGermanNumber($m[3]),
                    'betrag' => $this->parseGermanNumber($m[4]),
                ];
                $debugLogs[] = "[STRATEGY 3 MATCH] Line " . ($lineIdx + 1) . ": '{$nameAndQty}' (Price: {$m[2]} EUR)";
            }
        }

        if (empty($items)) {
            // Strategy 4: Multi-line OCR Block Scanning (for OCR layouts where columns are split across lines)
            $items = $this->extractMultiLineOcrItems($text, $debugLogs);
        } else {
            $debugLogs[] = "[STRATEGY 3 SUCCESS] Fallback scan extracted " . count($items) . " item(s).";
        }

        if (empty($items)) {
            $debugLogs[] = "[STRATEGY FAILED] Could not detect any item lines from PDF using strategies 1, 2, 3, or 4.";
        }

        return $items;
    }

    /**
     * Strategy 4: Multi-line OCR Block Scanning
     * Handles OCR output layouts where columns (Bezeichnung, Artikelnummer, Menge/Einheit, Preise)
     * are split into consecutive lines or separate block chunks.
     */
    protected function extractMultiLineOcrItems(string $text, array &$debugLogs): array
    {
        $debugLogs[] = "[STRATEGY 4] Trying multi-line OCR block scanning...";

        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($l) => $l !== ''
        ));

        // Step 1: Collect price blocks: e.g. "39,95 19,00 %"
        $priceBlocks = [];
        foreach ($lines as $line) {
            if (preg_match('/(?P<preis>\d+[\.,]\d{2})\s+(?P<ust>\d+(?:[\.,]\d+)?)\s*%/i', $line, $pm)) {
                $priceBlocks[] = [
                    'preis' => $this->parseGermanNumber($pm['preis']),
                    'ust' => $this->parseGermanNumber($pm['ust']),
                ];
            }
        }

        // Step 2: Pre-filter lines to remove headers/footers/commentaries
        $filteredLines = [];
        foreach ($lines as $line) {
            if (!preg_match('/^(?:Bezeichnung|Artikelnummer|Menge|Einheit|Preis|USt|Betrag|Summe|Zwischensumme|Endbetrag|Rechnung|Firma|Seite|Bitte|Wenn Sie|ANDREAS|Service|Sparkasse|IBAN|BIC|USt-ID)/i', $line)) {
                $filteredLines[] = $line;
            }
        }

        $items = [];
        $count = count($filteredLines);

        for ($i = 0; $i < $count; $i++) {
            $itemBezeichnung = '';
            $itemArtNr = '';
            $itemMenge = 1.0;
            $itemEinheit = 'Stück';
            $advance = 0;

            // Pattern A: 3 lines -> Line 1: [Name], Line 2: [ArtNr], Line 3: [Menge + Einheit]
            if ($i + 2 < $count) {
                $l1 = $filteredLines[$i];
                $l2 = $filteredLines[$i + 1];
                $l3 = $filteredLines[$i + 2];

                if (preg_match('/^[A-Za-z0-9\-_äöüÄÖÜ\s\.,&•()]{2,70}$/u', $l1) &&
                    preg_match('/^(?:Art\.?-?Nr\.?|Artikel-?Nr\.?|Artikel)?\s*([A-Za-z0-9\-_]{2,25})$/i', $l2, $artM) &&
                    preg_match('/^(\d+(?:[\.,]\d+)?)\s*(Stück|Stk\.?|Stiick|Kilogramm|kg|g|l|ml|m|Packung|Pck\.?|Beutel|Flasche|Dose|Ktn|Karton)$/i', $l3, $qtyM)) {

                    $itemBezeichnung = trim($l1);
                    $itemArtNr = trim($artM[1]);
                    $itemMenge = $this->parseGermanNumber($qtyM[1]);
                    $itemEinheit = trim($qtyM[2]);
                    $advance = 2;
                }
            }

            // Pattern B: 2 lines -> Line 1: [Name + ArtNr], Line 2: [Menge + Einheit]
            if (empty($itemBezeichnung) && $i + 1 < $count) {
                $l1 = $filteredLines[$i];
                $l2 = $filteredLines[$i + 1];

                if (preg_match('/^(?P<name>[^\r\n]+?)\s+(?P<artnr>[A-Za-z0-9\-_]{2,25})$/u', $l1, $nm) &&
                    preg_match('/^(\d+(?:[\.,]\d+)?)\s*(Stück|Stk\.?|Stiick|Kilogramm|kg|g|l|ml|m|Packung|Pck\.?|Beutel|Flasche|Dose|Ktn|Karton)$/i', $l2, $qtyM)) {

                    $itemBezeichnung = trim($nm['name']);
                    $itemArtNr = trim($nm['artnr']);
                    $itemMenge = $this->parseGermanNumber($qtyM[1]);
                    $itemEinheit = trim($qtyM[2]);
                    $advance = 1;
                }
            }

            // Pattern C: 4 lines -> Line 1: [Name], Line 2: [ArtNr], Line 3: [Menge + optional size/unit details], Line 4: [Einheit]
            if (empty($itemBezeichnung) && $i + 3 < $count) {
                $l1 = $filteredLines[$i];
                $l2 = $filteredLines[$i + 1];
                $l3 = $filteredLines[$i + 2];
                $l4 = $filteredLines[$i + 3];

                if (preg_match('/^[A-Za-z0-9\-_äöüÄÖÜ\s\.,&•()]{2,70}$/u', $l1) &&
                    preg_match('/^(?:Art\.?-?Nr\.?|Artikel-?Nr\.?|Artikel)?\s*([A-Za-z0-9\-_]{2,25})$/i', $l2, $artM) &&
                    preg_match('/^(\d+(?:[\.,]\d+)?)(?:\s+(\d+)\s*(?:g|ml|kg|l|m|Stück|Stk|Beutel|Stk\.?)?)?/i', $l3, $qtyM) &&
                    preg_match('/^(Stück|Stk\.?|Stiick|Kilogramm|kg|g|l|ml|m|Packung|Pck\.?|Beutel|Flasche|Dose|Ktn|Karton)$/i', $l4, $unitM)) {

                    $itemBezeichnung = trim($l1);
                    $itemArtNr = trim($artM[1]);
                    $itemMenge = $this->parseGermanNumber($qtyM[1]);
                    $itemEinheit = trim($unitM[1]);
                    $advance = 3;
                }
            }

            if (!empty($itemBezeichnung)) {
                $itemIndex = count($items);
                $pData = $priceBlocks[$itemIndex] ?? ['preis' => 0.0, 'ust' => 19.0];

                $items[] = [
                    'bezeichnung' => $itemBezeichnung,
                    'artikelnummer' => $itemArtNr,
                    'menge' => $itemMenge,
                    'einheit' => $itemEinheit,
                    'preis' => $pData['preis'],
                    'ust' => $pData['ust'],
                    'betrag' => round($itemMenge * $pData['preis'], 2),
                ];

                $debugLogs[] = "[STRATEGY 4 MATCH] Item #" . count($items) . ": '{$itemBezeichnung}' (ArtNr: '{$itemArtNr}', Qty: {$itemMenge} {$itemEinheit}, Price: {$pData['preis']} EUR)";
                $i += $advance;
            }
        }

        if (!empty($items)) {
            $debugLogs[] = "[STRATEGY 4 SUCCESS] Multi-line OCR block scan extracted " . count($items) . " item(s).";
        } else {
            $debugLogs[] = "[STRATEGY 4 FAILED] Multi-line OCR block scan found no items.";
        }

        return $items;
    }

    protected function cleanRawMatch(array $m): array
    {
        return [
            'bezeichnung' => trim($m['bezeichnung'] ?? ''),
            'artikelnummer' => trim($m['artnr'] ?? ''),
            'menge' => $this->parseGermanNumber($m['menge'] ?? '1'),
            'einheit' => trim($m['einheit'] ?? 'Stück'),
            'preis' => $this->parseGermanNumber($m['preis'] ?? '0'),
            'ust' => $this->parseGermanNumber($m['ust'] ?? '19'),
            'betrag' => $this->parseGermanNumber($m['betrag'] ?? '0'),
        ];
    }

    protected function parseGermanNumber(string $val): float
    {
        $val = str_replace(' ', '', $val);
        $val = str_replace('.', '', $val); // remove thousand separator
        $val = str_replace(',', '.', $val); // replace decimal comma
        return (float)$val;
    }

    /**
     * Analyze a single extracted item against the warehouse catalog.
     */
    protected function analyzeItem(array $raw, $allWarehouseItems, array &$debugLogs, int $itemNum): array
    {
        $invoiceName = $raw['bezeichnung'];
        $invoiceArtNr = $raw['artikelnummer'];
        $invoicePrice = (float)$raw['preis'];

        $matchedItem = null;
        $status = 'not_found';
        $similarityPercent = 0;
        $matchReason = '';

        // 1. Exact Match Check (by neue_artikelnummer, alte_artikelnummer, or exact bezeichnung)
        if (!empty($invoiceArtNr)) {
            $matchedItem = $allWarehouseItems->first(function ($item) use ($invoiceArtNr) {
                return strcasecmp($item->neue_artikelnummer, $invoiceArtNr) === 0 ||
                       strcasecmp($item->alte_artikelnummer ?? '', $invoiceArtNr) === 0;
            });
            if ($matchedItem) {
                $status = 'exact_match';
                $matchReason = 'Artikelnummer exakt übereinstimmend (' . $invoiceArtNr . ')';
                $similarityPercent = 100;
            }
        }

        if (!$matchedItem && !empty($invoiceName)) {
            $matchedItem = $allWarehouseItems->first(function ($item) use ($invoiceName) {
                return strcasecmp(trim($item->bezeichnung), trim($invoiceName)) === 0;
            });
            if ($matchedItem) {
                $status = 'exact_match';
                $matchReason = 'Bezeichnung exakt übereinstimmend';
                $similarityPercent = 100;
            }
        }

        // 2. Fuzzy Match Check (if exact match failed)
        if (!$matchedItem && !empty($invoiceName)) {
            $bestScore = 0;
            $bestCandidate = null;

            foreach ($allWarehouseItems as $item) {
                similar_text(mb_strtolower($invoiceName), mb_strtolower($item->bezeichnung), $percent);
                if ($percent > $bestScore) {
                    $bestScore = $percent;
                    $bestCandidate = $item;
                }
            }

            // Threshold for fuzzy match: 65% similarity
            if ($bestCandidate && $bestScore >= 65.0) {
                $matchedItem = $bestCandidate;
                $status = 'fuzzy_match';
                $similarityPercent = round($bestScore, 1);
                $matchReason = "Ähnlicher Artikelname ('{$bestCandidate->bezeichnung}', {$similarityPercent}% Übereinstimmung)";
            }
        }

        // Price comparison
        $priceMatch = false;
        $priceDifference = null;

        if ($matchedItem) {
            $warehouseEk = (float)$matchedItem->ek_ohne_st;
            $warehouseVk = (float)$matchedItem->vk_ohne_st;

            // Check if invoice net price matches EK (or VK) within 0.01
            if (abs($invoicePrice - $warehouseEk) <= 0.01 || abs($invoicePrice - $warehouseVk) <= 0.01) {
                $priceMatch = true;
            } else {
                $priceMatch = false;
                $priceDifference = sprintf("Rechnungspreis %.2f € Weicht von Lager-EK %.2f € ab", $invoicePrice, $warehouseEk);
            }
        }

        $debugLogs[] = "[MATCH ITEM #{$itemNum}] '{$invoiceName}' (ArtNr: '{$invoiceArtNr}') -> Status: '{$status}', Reason: " . ($matchReason ?: 'Kein Treffer im Lagerbestand');

        return [
            'invoice_bezeichnung' => $invoiceName,
            'invoice_artikelnummer' => $invoiceArtNr,
            'invoice_menge' => (int)max(1, round($raw['menge'])),
            'invoice_einheit' => $raw['einheit'] ?: 'Stück',
            'invoice_netto_preis' => $invoicePrice,
            'invoice_ust' => (float)$raw['ust'],
            'invoice_betrag' => (float)$raw['betrag'],
            'status' => $status,
            'similarity_percent' => $similarityPercent,
            'match_reason' => $matchReason,
            'matched_item' => $matchedItem ? [
                'id' => $matchedItem->id,
                'bezeichnung' => $matchedItem->bezeichnung,
                'neue_artikelnummer' => $matchedItem->neue_artikelnummer,
                'stueckzahl' => $matchedItem->stueckzahl,
                'ek_ohne_st' => (float)$matchedItem->ek_ohne_st,
                'vk_ohne_st' => (float)$matchedItem->vk_ohne_st,
            ] : null,
            'price_match' => $priceMatch,
            'price_difference' => $priceDifference,
        ];
    }

    /**
     * Find absolute path to a CLI binary across common Linux and macOS paths.
     */
    protected function findBinary(array $candidates): ?string
    {
        foreach ($candidates as $cand) {
            if (str_contains($cand, '/') && file_exists($cand) && is_executable($cand)) {
                return $cand;
            }
            $which = trim((string)shell_exec(sprintf('which %s 2>/dev/null', escapeshellarg($cand))));
            if (!empty($which) && file_exists($which) && is_executable($which)) {
                return $which;
            }
        }

        return null;
    }
}


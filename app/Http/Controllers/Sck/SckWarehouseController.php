<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckWarehouseItem;
use App\Models\Sck\SckWarehouseLog;
use App\Services\DatevInvoiceParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SckWarehouseController extends Controller
{
    /**
     * Display a listing of the warehouse items with search & sorting.
     */
    public function index(Request $request)
    {
        $query = SckWarehouseItem::query();

        // Search filtering
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('bezeichnung', 'like', "%{$search}%")
                  ->orWhere('geraet', 'like', "%{$search}%")
                  ->orWhere('lieferant', 'like', "%{$search}%")
                  ->orWhere('alte_artikelnummer', 'like', "%{$search}%")
                  ->orWhere('neue_artikelnummer', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'bezeichnung');
        $sortDir = $request->input('sort_dir', 'asc');
        
        $allowedSorts = ['bezeichnung', 'geraet', 'artikelgruppe', 'lieferant', 'ek_ohne_st', 'vk_ohne_st', 'stueckzahl', 'neue_artikelnummer'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('bezeichnung', 'asc');
        }

        $items = $query->paginate(15)->withQueryString();

        // Multi-select tracking IDs
        $pageIds = $items->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $matchingIds = (clone $query)->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $selectedIds = session('sck_warehouse_selected_ids', []);

        // Gather unique device categories for filters / stats
        $categories = SckWarehouseItem::select('geraet')->distinct()->pluck('geraet');

        $logs = SckWarehouseLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(500)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'item_id' => $log->item_id,
                    'success' => (bool)$log->success,
                    'action' => $log->action,
                    'type' => $log->type,
                    'message' => $log->message,
                    'time' => $log->created_at->timezone('Europe/Berlin')->format('d.m.Y H:i:s'),
                ];
            });

        $showDatevStatus = (bool)session('sck_warehouse_show_datev_status', false);

        return view('sck.warehouse.index', compact('items', 'categories', 'logs', 'pageIds', 'matchingIds', 'selectedIds', 'showDatevStatus'));
    }

    /**
     * Store DATEV status column visibility preference in session.
     */
    public function storeDatevStatusToggle(Request $request)
    {
        $show = filter_var($request->input('show'), FILTER_VALIDATE_BOOLEAN);
        session(['sck_warehouse_show_datev_status' => $show]);
        return response()->json(['success' => true]);
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bezeichnung' => 'required|string|max:255',
            'geraet' => 'required|string|max:255',
            'artikelgruppe' => 'nullable|string|max:255',
            'einheit' => 'nullable|string|max:255',
            'steuersatz' => 'nullable|string|max:50',
            'lieferant' => 'required|string|max:255',
            'ek_ohne_st' => 'required|numeric|min:0',
            'vk_ohne_st' => 'required|numeric|min:0',
            'alte_artikelnummer' => 'nullable|string|max:255',
            'neue_artikelnummer' => 'nullable|string|digits:5|unique:sck_warehouse_items,neue_artikelnummer',
            'stueckzahl' => 'required|integer|min:0',
            'kommentar' => 'nullable|string',
        ]);

        if (empty($validated['einheit'])) {
            $validated['einheit'] = 'Stück';
        }
        if (empty($validated['steuersatz'])) {
            $validated['steuersatz'] = '19';
        }

        if (isset($validated['artikelgruppe']) && strcasecmp($validated['artikelgruppe'], 'Dienstleistung') === 0) {
            $validated['stueckzahl'] = 0;
        }

        // If no custom number was provided, the model's boot() will auto-generate one
        $item = SckWarehouseItem::create($validated);

        return redirect()->route('sck.lager.index')->with('success', "Artikel '{$item->bezeichnung}' wurde erfolgreich mit der Artikelnummer {$item->neue_artikelnummer} angelegt.");
    }

    /**
     * Quick stock updates from the dashboard (forms).
     */
    public function updateStock(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:sck_warehouse_items,id',
            'action' => 'required|in:add,remove',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = SckWarehouseItem::findOrFail($request->item_id);
        if ($item->is_dienstleistung) {
            return redirect()->back()->with('error', 'Fehler: Für Dienstleistungen kann kein Bestand gepflegt werden.');
        }
        $qty = intval($request->quantity);

        if ($request->action === 'add') {
            $item->stueckzahl += $qty;
            $msg = "{$qty}x '{$item->bezeichnung}' erfolgreich aufgefüllt.";
        } else {
            if ($item->stueckzahl < $qty) {
                $errorMsg = "Fehler: Es können nicht {$qty}x '{$item->bezeichnung}' entnommen werden, da nur {$item->stueckzahl}x vorhanden sind.";
                SckWarehouseLog::create([
                    'user_id' => auth()->id(),
                    'item_id' => $item->id,
                    'success' => false,
                    'action' => 'remove',
                    'type' => 'quick',
                    'message' => $errorMsg
                ]);
                return redirect()->back()->with('error', $errorMsg);
            }
            $item->stueckzahl -= $qty;
            $msg = "{$qty}x '{$item->bezeichnung}' erfolgreich entnommen.";
        }

        $item->save();

        SckWarehouseLog::create([
            'user_id' => auth()->id(),
            'item_id' => $item->id,
            'success' => true,
            'action' => $request->action,
            'type' => 'quick',
            'message' => $msg
        ]);

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Export the product list as a CSV file.
     */
    public function export(Request $request)
    {
        $includeStock = $request->input('include_stock', '1') === '1';
        $items = SckWarehouseItem::orderBy('bezeichnung', 'asc')->get();

        $fileName = 'SCK_Lagerliste_' . ($includeStock ? 'mit_bestand_' : 'ohne_bestand_') . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($items, $includeStock) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 Byte Order Mark (BOM) to force Excel to read UTF-8 correctly
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header Row
            $columns = [
                'Bezeichnung',
                'Gerät/Kategorie',
                'Lieferant',
                'EK ohne St. (€)',
                'VK ohne St. (€)',
                'Alte Artikelnummer',
                'Neue Artikelnummer',
            ];

            if ($includeStock) {
                $columns[] = 'Stückzahl';
            }

            $columns[] = 'Kommentar';

            fputcsv($file, $columns, ';');

            foreach ($items as $item) {
                $row = [
                    $item->bezeichnung,
                    $item->geraet,
                    $item->lieferant,
                    number_format((float)$item->ek_ohne_st, 2, ',', ''),
                    number_format((float)$item->vk_ohne_st, 2, ',', ''),
                    $item->alte_artikelnummer ?? '',
                    $item->neue_artikelnummer,
                ];

                if ($includeStock) {
                    $row[] = $item->stueckzahl;
                }

                $row[] = $item->kommentar ?? '';

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Render the specific mobile-optimized scan page.
     */
    public function scanPage()
    {
        $logs = SckWarehouseLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(500)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'item_id' => $log->item_id,
                    'success' => (bool)$log->success,
                    'action' => $log->action,
                    'type' => $log->type,
                    'message' => $log->message,
                    'time' => $log->created_at->timezone('Europe/Berlin')->format('d.m.Y H:i:s'),
                ];
            });

        return view('sck.warehouse.scan', compact('logs'));
    }

    /**
     * Clear all database activity logs.
     */
    public function clearLogs()
    {
        SckWarehouseLog::truncate();
        return response()->json(['success' => true]);
    }

    /**
     * AJAX/API endpoint to process mobile scans.
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'action' => 'required|in:add,remove',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = SckWarehouseItem::where('neue_artikelnummer', $request->barcode)->first();

        if (!$item) {
            $msg = "Artikel mit Nummer '{$request->barcode}' wurde im System nicht gefunden.";
            SckWarehouseLog::create([
                'user_id' => auth()->id(),
                'item_id' => null,
                'success' => false,
                'action' => $request->action,
                'type' => 'scanner',
                'message' => $msg
            ]);
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 404);
        }

        if ($item->is_dienstleistung) {
            $msg = "✖ Fehler: Für Dienstleistungen ('{$item->bezeichnung}') kann kein Bestand gebucht werden.";
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 400);
        }

        $qty = intval($request->quantity);

        if ($request->action === 'add') {
            $item->stueckzahl += $qty;
            $item->save();
            $msg = "✓ {$qty}x '{$item->bezeichnung}' erfolgreich eingebucht. Neuer Bestand: {$item->stueckzahl} Stk.";
            SckWarehouseLog::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'success' => true,
                'action' => 'add',
                'type' => 'scanner',
                'message' => $msg
            ]);
            return response()->json([
                'success' => true,
                'item_name' => $item->bezeichnung,
                'action_label' => 'eingebucht',
                'quantity' => $qty,
                'new_stock' => $item->stueckzahl,
                'message' => $msg
            ]);
        } else {
            if ($item->stueckzahl < $qty) {
                $msg = "✖ Fehler: Es können nicht {$qty}x '{$item->bezeichnung}' ausgebucht werden (Bestand: {$item->stueckzahl} Stk.).";
                SckWarehouseLog::create([
                    'user_id' => auth()->id(),
                    'item_id' => $item->id,
                    'success' => false,
                    'action' => 'remove',
                    'type' => 'scanner',
                    'message' => $msg
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $msg
                ], 400);
            }

            $item->stueckzahl -= $qty;
            $item->save();
            $msg = "✓ {$qty}x '{$item->bezeichnung}' erfolgreich ausgebucht. Neuer Bestand: {$item->stueckzahl} Stk.";
            SckWarehouseLog::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'success' => true,
                'action' => 'remove',
                'type' => 'scanner',
                'message' => $msg
            ]);
            return response()->json([
                'success' => true,
                'item_name' => $item->bezeichnung,
                'action_label' => 'ausgebucht',
                'quantity' => $qty,
                'new_stock' => $item->stueckzahl,
                'message' => $msg
            ]);
        }
    }

    /**
     * Display details for a specific item (for scans outside the app).
     */
    public function show($neue_artikelnummer)
    {
        $showItem = SckWarehouseItem::where('neue_artikelnummer', $neue_artikelnummer)->firstOrFail();

        $query = SckWarehouseItem::query();
        $items = $query->paginate(15)->withQueryString();
        $categories = SckWarehouseItem::select('geraet')->distinct()->pluck('geraet');

        $logs = SckWarehouseLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(500)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'item_id' => $log->item_id,
                    'success' => (bool)$log->success,
                    'action' => $log->action,
                    'type' => $log->type,
                    'message' => $log->message,
                    'time' => $log->created_at->timezone('Europe/Berlin')->format('d.m.Y H:i:s'),
                ];
            });

        return view('sck.warehouse.index', compact('items', 'categories', 'showItem', 'logs'));
    }

    /**
     * Update an existing item.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:sck_warehouse_items,id',
            'bezeichnung' => 'required|string|max:255',
            'geraet' => 'required|string|max:255',
            'artikelgruppe' => 'nullable|string|max:255',
            'einheit' => 'nullable|string|max:255',
            'steuersatz' => 'nullable|string|max:50',
            'lieferant' => 'required|string|max:255',
            'ek_ohne_st' => 'required|numeric|min:0',
            'vk_ohne_st' => 'required|numeric|min:0',
            'alte_artikelnummer' => 'nullable|string|max:255',
            'neue_artikelnummer' => 'nullable|string|digits:5|unique:sck_warehouse_items,neue_artikelnummer,' . $request->id,
            'stueckzahl' => 'required|integer|min:0',
            'kommentar' => 'nullable|string',
        ]);

        if (empty($validated['einheit'])) {
            $validated['einheit'] = 'Stück';
        }
        if (empty($validated['steuersatz'])) {
            $validated['steuersatz'] = '19';
        }

        if (isset($validated['artikelgruppe']) && strcasecmp($validated['artikelgruppe'], 'Dienstleistung') === 0) {
            $validated['stueckzahl'] = 0;
        }

        $item = SckWarehouseItem::findOrFail($request->id);
        $oldStock = $item->stueckzahl;
        $newStock = intval($validated['stueckzahl']);

        $item->update($validated);

        if ($oldStock !== $newStock && !$item->is_dienstleistung) {
            $diff = $newStock - $oldStock;
            $action = $diff > 0 ? 'add' : 'remove';
            $diffAbs = abs($diff);
            $actionLabel = $diff > 0 ? 'manuell erhöht' : 'manuell verringert';
            $msg = "✓ Lagerbestand von '{$item->bezeichnung}' {$actionLabel} um {$diffAbs} Stk. Neuer Bestand: {$newStock} Stk.";

            SckWarehouseLog::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'success' => true,
                'action' => $action,
                'type' => 'manual',
                'message' => $msg
            ]);
        }

        return redirect()->route('sck.lager.index')->with('success', "Artikel '{$item->bezeichnung}' wurde erfolgreich aktualisiert.");
    }

    /**
     * Generate and return a unique 5-digit article number as JSON.
     */
    public function generateNumber()
    {
        return response()->json([
            'number' => SckWarehouseItem::generateUniqueArticleNumber()
        ]);
    }

    /**
     * Search items for template baselines and return JSON.
     */
    public function searchJson(Request $request)
    {
        $search = $request->input('q');
        if (empty($search)) {
            return response()->json([]);
        }

        $items = SckWarehouseItem::where('bezeichnung', 'like', "%{$search}%")
            ->orWhere('neue_artikelnummer', 'like', "%{$search}%")
            ->orWhere('alte_artikelnummer', 'like', "%{$search}%")
            ->limit(10)
            ->get();

        return response()->json($items);
    }

    /**
    /**
     * Export the product list as a DATEV-compatible CSV file.
     */
    public function exportDatev()
    {
        $items = SckWarehouseItem::where('datev_exported', false)->orderBy('bezeichnung', 'asc')->get();

        $fileName = 'DATEV_Artikelimport_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header Row (exact columns matching CSV_Vorlage_Artikelimport_Nettopreise.csv)
            $columns = [
                'Art.-Nr.',
                'Bezeichnung',
                'Zusätzliche Beschreibung',
                'Artikelgruppe',
                'Artikeltyp',
                'Bezeichnung_Steuersatz',
                'Nettopreis',
                'Einheit_(Einzahl)',
                'Einheit_(Mehrzahl)',
                'Rabattfähig'
            ];

            fputcsv($file, $columns, ';');

            foreach ($items as $item) {
                // Map steuersatz to template name
                $steuersatzStr = 'Volle Steuer';
                if ($item->steuersatz === '7') {
                    $steuersatzStr = 'Ermäßigte Steuer';
                } elseif ($item->steuersatz === '0') {
                    $steuersatzStr = 'Steuerfrei';
                }

                $einheitSingular = $item->einheit ?: 'Stück';
                $einheitPlural = $einheitSingular . 'n';
                if ($einheitSingular === 'Stück') {
                    $einheitPlural = 'Stücke';
                }

                $row = [
                    $item->neue_artikelnummer,
                    $item->bezeichnung,
                    $item->kommentar ?? '',
                    $item->artikelgruppe ?? '',
                    'Ware',
                    $steuersatzStr,
                    number_format((float)$item->vk_ohne_st, 4, ',', ''),
                    $einheitSingular,
                    $einheitPlural,
                    'ja'
                ];

                fputcsv($file, $row, ';');

                // Mark as exported
                $item->update(['datev_exported' => true]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Store selected IDs in session for bulk actions.
     */
    public function storeBulkSelection(Request $request)
    {
        $ids = $request->input('ids', []);
        session(['sck_warehouse_selected_ids' => $ids]);
        return response()->json(['success' => true]);
    }

    /**
     * Bulk delete selected warehouse items.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Keine Artikel zum Löschen ausgewählt.');
        }

        $count = SckWarehouseItem::whereIn('id', $ids)->delete();
        session()->forget('sck_warehouse_selected_ids');

        SckWarehouseLog::create([
            'user_id' => auth()->id(),
            'item_id' => null,
            'success' => true,
            'action' => 'delete',
            'type' => 'manual',
            'message' => "Massenlöschung: {$count} Artikel wurden aus dem Lagersystem gelöscht."
        ]);

        return redirect()->route('sck.lager.index')->with('success', "{$count} Artikel wurden erfolgreich gelöscht.");
    }

    /**
     * AJAX endpoint to upload and parse a DATEV PDF invoice.
     */
    public function parseInvoice(Request $request, DatevInvoiceParserService $parser)
    {
        $request->validate([
            'invoice_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('invoice_file');
        $tempPath = $file->getRealPath();
        $fileName = $file->getClientOriginalName();

        Log::info("SckWarehouseController: Invoice parse requested", ['file' => $fileName]);

        try {
            $analysis = $parser->parsePdf($tempPath);

            Log::info("SckWarehouseController: Invoice PDF successfully parsed", [
                'file' => $fileName,
                'detected_items' => count($analysis['items'] ?? []),
                'invoice_info' => $analysis['invoice_info'] ?? [],
            ]);

            return response()->json($analysis);
        } catch (\Throwable $e) {
            Log::error("SckWarehouseController: Invoice PDF parsing failed", [
                'file' => $fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Analysieren der DATEV-Rechnung: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Process final inventory deduction from parsed invoice data.
     */
    public function processInvoiceDeduction(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:sck_warehouse_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.update_price' => 'nullable|boolean',
            'items.*.new_price' => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string',
        ]);

        $invoiceNum = $request->input('invoice_number', 'ausstehend');
        $deductedCount = 0;
        $messages = [];

        foreach ($request->input('items') as $entry) {
            $item = SckWarehouseItem::findOrFail($entry['item_id']);
            $qty = (int)$entry['quantity'];
            $oldStock = $item->stueckzahl;
            
            // Deduct stock
            if ($item->is_dienstleistung) {
                $item->stueckzahl = 0;
            } else {
                $item->stueckzahl = max(0, $item->stueckzahl - $qty);
            }

            // Optional price update
            if (!empty($entry['update_price']) && isset($entry['new_price'])) {
                $item->ek_ohne_st = (float)$entry['new_price'];
            }

            $item->save();
            $deductedCount++;

            if ($item->is_dienstleistung) {
                $logMsg = "✓ Rechnungsabzug (Rechnung #{$invoiceNum}): {$qty}x '{$item->bezeichnung}' (Dienstleistung - kein Bestandsabzug erforderlich)";
            } else {
                $logMsg = "✓ Rechnungsabzug (Rechnung #{$invoiceNum}): {$qty}x '{$item->bezeichnung}' entnommen (Bestand: {$oldStock} -> {$item->stueckzahl} Stk.)";
            }
            if (!empty($entry['update_price']) && isset($entry['new_price'])) {
                $logMsg .= sprintf(" | EK-Preis angepasst auf %.2f €", (float)$entry['new_price']);
            }

            SckWarehouseLog::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'success' => true,
                'action' => 'remove',
                'type' => 'invoice',
                'message' => $logMsg
            ]);

            $messages[] = "{$qty}x '{$item->bezeichnung}'";
        }

        $summary = "Rechnungsabzug erfolgreich durchgeführt: " . implode(', ', $messages) . " aus dem Bestand entnommen.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $summary
            ]);
        }

        return redirect()->route('sck.lager.index')->with('success', $summary);
    }

    /**
     * Bulk export selected items as CSV.
     */
    public function bulkExport(Request $request)
    {
        $ids = $request->input('ids', []);
        $includeStock = $request->input('include_stock', '1') === '1';

        $query = SckWarehouseItem::orderBy('bezeichnung', 'asc');
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }
        $items = $query->get();

        $fileName = 'SCK_Auswahl_Export_' . ($includeStock ? 'mit_bestand_' : 'ohne_bestand_') . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($items, $includeStock) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            $columns = ['Bezeichnung', 'Gerät/Kategorie', 'Lieferant', 'EK ohne St. (€)', 'VK ohne St. (€)', 'Alte Artikelnummer', 'Neue Artikelnummer'];
            if ($includeStock) $columns[] = 'Stückzahl';
            $columns[] = 'Kommentar';

            fputcsv($file, $columns, ';');

            foreach ($items as $item) {
                $row = [
                    $item->bezeichnung,
                    $item->geraet,
                    $item->lieferant,
                    number_format((float)$item->ek_ohne_st, 2, ',', ''),
                    number_format((float)$item->vk_ohne_st, 2, ',', ''),
                    $item->alte_artikelnummer ?? '',
                    $item->neue_artikelnummer,
                ];
                if ($includeStock) $row[] = $item->stueckzahl;
                $row[] = $item->kommentar ?? '';

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
    /**
     * Bulk export selected items in DATEV CSV format.
     */
    public function bulkExportDatev(Request $request)
    {
        $ids = $request->input('ids', []);

        $query = SckWarehouseItem::where('datev_exported', false)->orderBy('bezeichnung', 'asc');
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }
        $items = $query->get();

        $fileName = 'DATEV_Auswahl_Export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            // CSV Header Row (exact columns matching CSV_Vorlage_Artikelimport_Nettopreise.csv)
            $columns = [
                'Art.-Nr.',
                'Bezeichnung',
                'Zusätzliche Beschreibung',
                'Artikelgruppe',
                'Artikeltyp',
                'Bezeichnung_Steuersatz',
                'Nettopreis',
                'Einheit_(Einzahl)',
                'Einheit_(Mehrzahl)',
                'Rabattfähig'
            ];

            fputcsv($file, $columns, ';');

            foreach ($items as $item) {
                $steuersatzStr = 'Volle Steuer';
                if ($item->steuersatz === '7') {
                    $steuersatzStr = 'Ermäßigte Steuer';
                } elseif ($item->steuersatz === '0') {
                    $steuersatzStr = 'Steuerfrei';
                }

                $einheitSingular = $item->einheit ?: 'Stück';
                $einheitPlural = $einheitSingular . 'n';
                if ($einheitSingular === 'Stück') {
                    $einheitPlural = 'Stücke';
                }

                $row = [
                    $item->neue_artikelnummer,
                    $item->bezeichnung,
                    $item->kommentar ?? '',
                    $item->artikelgruppe ?? '',
                    'Ware',
                    $steuersatzStr,
                    number_format((float)$item->vk_ohne_st, 4, ',', ''),
                    $einheitSingular,
                    $einheitPlural,
                    'ja'
                ];

                fputcsv($file, $row, ';');

                // Mark as exported
                $item->update(['datev_exported' => true]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Toggle the DATEV export status for a single item.
     */
    public function toggleDatevExported($id)
    {
        $item = SckWarehouseItem::findOrFail($id);
        $item->datev_exported = !$item->datev_exported;
        $item->save();

        $statusStr = $item->datev_exported ? 'als exportiert markiert' : 'als nicht exportiert markiert';
        
        SckWarehouseLog::create([
            'user_id' => auth()->id(),
            'item_id' => $item->id,
            'success' => true,
            'action' => 'manual',
            'type' => 'manual',
            'message' => "Artikel '{$item->bezeichnung}' {$statusStr}."
        ]);

        return redirect()->back()->with('success', "Status für '{$item->bezeichnung}' erfolgreich geändert.");
    }

    /**
     * Bulk toggle or set the DATEV export status for selected items.
     */
    public function bulkToggleDatevExported(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Keine Artikel ausgewählt.');
        }

        $items = SckWarehouseItem::whereIn('id', $ids)->get();
        $count = $items->count();

        foreach ($items as $item) {
            $item->datev_exported = $status;
            $item->save();

            $statusStr = $status ? 'als exportiert markiert' : 'als nicht exportiert markiert';

            SckWarehouseLog::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'success' => true,
                'action' => 'manual',
                'type' => 'manual',
                'message' => "Massenaktion: '{$item->bezeichnung}' {$statusStr}."
            ]);
        }

        $msg = $status 
            ? "{$count} Artikel wurden erfolgreich als DATEV-exportiert markiert."
            : "Export-Status von {$count} Artikeln wurde erfolgreich zurückgesetzt.";

        return redirect()->route('sck.lager.index')->with('success', $msg);
    }

    /**
     * Bulk update or reset stock for selected items.
     */
    public function bulkUpdateStock(Request $request)
    {
        $ids = $request->input('ids', []);
        $mode = $request->input('mode', 'set'); // 'set' or 'add'
        $amount = (int)$request->input('amount', 0);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Keine Artikel für die Bestandsänderung ausgewählt.');
        }

        $items = SckWarehouseItem::whereIn('id', $ids)->get();
        $count = $items->count();

        foreach ($items as $item) {
            $oldStock = $item->stueckzahl;
            if ($mode === 'set') {
                $item->stueckzahl = max(0, $amount);
            } else {
                $item->stueckzahl = max(0, $item->stueckzahl + $amount);
            }
            $item->save();

            SckWarehouseLog::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'success' => true,
                'action' => $mode === 'set' ? 'manual' : ($amount >= 0 ? 'add' : 'remove'),
                'type' => 'manual',
                'message' => "Massen-Bestandsanpassung: '{$item->bezeichnung}' Bestand von {$oldStock} auf {$item->stueckzahl} Stk. geändert."
            ]);
        }

        $msg = $mode === 'set' && $amount === 0 
            ? "Lagerbestand von {$count} markierten Artikeln wurde auf 0 zurückgesetzt."
            : "Lagerbestand von {$count} markierten Artikeln wurde erfolgreich angepasst.";

        return redirect()->route('sck.lager.index')->with('success', $msg);
    }
}


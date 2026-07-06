<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckWarehouseItem;
use Illuminate\Http\Request;
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
        
        $allowedSorts = ['bezeichnung', 'geraet', 'lieferant', 'ek_ohne_st', 'vk_ohne_st', 'stueckzahl', 'neue_artikelnummer'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('bezeichnung', 'asc');
        }

        $items = $query->paginate(30)->withQueryString();

        // Gather unique device categories for filters / stats
        $categories = SckWarehouseItem::select('geraet')->distinct()->pluck('geraet');

        return view('sck.warehouse.index', compact('items', 'categories'));
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bezeichnung' => 'required|string|max:255',
            'geraet' => 'required|string|max:255',
            'lieferant' => 'required|string|max:255',
            'ek_ohne_st' => 'required|numeric|min:0',
            'vk_ohne_st' => 'required|numeric|min:0',
            'alte_artikelnummer' => 'nullable|string|max:255',
            'neue_artikelnummer' => 'nullable|string|digits:5|unique:sck_warehouse_items,neue_artikelnummer',
            'stueckzahl' => 'required|integer|min:0',
            'kommentar' => 'nullable|string',
        ]);

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
        $qty = intval($request->quantity);

        if ($request->action === 'add') {
            $item->stueckzahl += $qty;
            $msg = "{$qty}x '{$item->bezeichnung}' erfolgreich aufgefüllt.";
        } else {
            if ($item->stueckzahl < $qty) {
                return redirect()->back()->with('error', "Fehler: Es können nicht {$qty}x '{$item->bezeichnung}' entnommen werden, da nur {$item->stueckzahl}x vorhanden sind.");
            }
            $item->stueckzahl -= $qty;
            $msg = "{$qty}x '{$item->bezeichnung}' erfolgreich entnommen.";
        }

        $item->save();

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
        return view('sck.warehouse.scan');
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
            return response()->json([
                'success' => false,
                'message' => "Artikel mit Nummer '{$request->barcode}' wurde im System nicht gefunden."
            ], 404);
        }

        $qty = intval($request->quantity);

        if ($request->action === 'add') {
            $item->stueckzahl += $qty;
            $item->save();
            return response()->json([
                'success' => true,
                'item_name' => $item->bezeichnung,
                'action_label' => 'eingebucht',
                'quantity' => $qty,
                'new_stock' => $item->stueckzahl,
                'message' => "✓ {$qty}x '{$item->bezeichnung}' erfolgreich eingebucht. Neuer Bestand: {$item->stueckzahl} Stk."
            ]);
        } else {
            if ($item->stueckzahl < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => "✖ Fehler: Es können nicht {$qty}x '{$item->bezeichnung}' ausgebucht werden (Bestand: {$item->stueckzahl} Stk.)."
                ], 400);
            }

            $item->stueckzahl -= $qty;
            $item->save();
            return response()->json([
                'success' => true,
                'item_name' => $item->bezeichnung,
                'action_label' => 'ausgebucht',
                'quantity' => $qty,
                'new_stock' => $item->stueckzahl,
                'message' => "✓ {$qty}x '{$item->bezeichnung}' erfolgreich ausgebucht. Neuer Bestand: {$item->stueckzahl} Stk."
            ]);
        }
    }

    /**
     * Display details for a specific item (for scans outside the app).
     */
    public function show($neue_artikelnummer)
    {
        $item = SckWarehouseItem::where('neue_artikelnummer', $neue_artikelnummer)->firstOrFail();
        return view('sck.warehouse.show', compact('item'));
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
            'lieferant' => 'required|string|max:255',
            'ek_ohne_st' => 'required|numeric|min:0',
            'vk_ohne_st' => 'required|numeric|min:0',
            'alte_artikelnummer' => 'nullable|string|max:255',
            'neue_artikelnummer' => 'nullable|string|digits:5|unique:sck_warehouse_items,neue_artikelnummer,' . $request->id,
            'stueckzahl' => 'required|integer|min:0',
            'kommentar' => 'nullable|string',
        ]);

        $item = SckWarehouseItem::findOrFail($request->id);
        $item->update($validated);

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
}

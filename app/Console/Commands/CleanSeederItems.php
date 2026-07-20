<?php

namespace App\Console\Commands;

use App\Models\Sck\SckWarehouseItem;
use App\Models\Bar;
use App\Models\Drink;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanSeederItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean-seeder-items 
                            {--dry-run : Preview which items will be deleted without actually removing them} 
                            {--include-other : Also delete dummy records from Bar, Drink, and Page seeders}
                            {--detect-batches : Detect and clean ALL historical seeder batches using timestamp clustering}
                            {--force : Force deletion without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely drop seeded dummy items from the database without touching user-created data.';

    /**
     * List of seeded warehouse item old article numbers and designations from the latest seeder.
     */
    protected array $seededArticleNumbers = [
        'JU-BG-S9',
        'JU-MW-V5',
        'GM-DS-10',
        'UL-EX5-230',
        'DL-TB-1200',
        'JP-AV-MS',
        'GM-EK-1L',
        'GM-RT-100',
        'JU-DS-FEP',
        'DL-SERV-KAFF',
        'OL-MB-500',
        'GR-BS-150',
        'GR-BK-SCHW',
        'OL-AK-ES',
        'SH-HFS-W',
        'GE-RSS-40',
        'SD-SIL-SAN',
        'OL-NK-SCHW',
        'DL-SERV-WASCH',
    ];

    protected array $seededBezeichnungen = [
        'Brühgruppe komplett',
        'Mahlwerk V5 mit Motor',
        'Dichtungssatz Premium O-Ringe (10er Set)',
        'Vibrationspumpe EX 5 230V 48W',
        'Thermoblock Erhitzer 230V 1200W',
        'Auslaufventil Messing Upgrade',
        'Flüssigentkalker Premium 1L',
        'Reinigungstabletten 2g (100er Dose)',
        'Druckschlauch FEP 4x2mm (5m Rolle)',
        'Servicepauschale Kaffeevollautomat',
        'Einhebel-Mischbatterie Friseurbecken',
        'Friseur-Brauseschlauch 150cm schwarz',
        'Profi-Brausekopf schwarz mit Sparventil',
        'Ablaufkelch mit Haarsieb Edelstahl',
        'Haarfangsieb Kunststoff weiß',
        'Flexibler Raumsparsifon Ø40mm',
        'Sanitär-Silikon Transparent 310ml',
        'Nackenkissen Gummi schwarz',
        'Servicepauschale Friseursalon Montage',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        $includeOther = $this->option('include-other');
        $detectBatches = $this->option('detect-batches');

        $warehouseItems = collect();

        if ($detectBatches) {
            $this->info("Scanning database for seeder batches via timestamp clustering...");

            // Find timestamps where 5 or more items were created in the exact same second
            $batches = SckWarehouseItem::select('created_at', DB::raw('count(*) as count'))
                ->groupBy('created_at')
                ->having('count', '>=', 5)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($batches->isEmpty()) {
                $this->info("No bulk-created batches (5+ items in the same second) were detected.");
            } else {
                $this->info("Detected " . $batches->count() . " batch(es):");
                foreach ($batches as $batch) {
                    $this->line(" - Timestamp: <fg=cyan>{$batch->created_at}</> | Count: <fg=yellow>{$batch->count} items</>");
                    
                    // Fetch items in this batch
                    $itemsInBatch = SckWarehouseItem::where('created_at', $batch->created_at)->get();
                    $warehouseItems = $warehouseItems->merge($itemsInBatch);
                }
            }
        } else {
            // Default: Match by known seeder signatures
            $warehouseItems = SckWarehouseItem::whereIn('alte_artikelnummer', $this->seededArticleNumbers)
                ->orWhereIn('bezeichnung', $this->seededBezeichnungen)
                ->get();
        }

        // De-duplicate if items were matched by both
        $warehouseItems = $warehouseItems->unique('id');

        $this->info("Found {$warehouseItems->count()} SCK Warehouse seeder item(s) in total.");

        if ($warehouseItems->isNotEmpty()) {
            $tableData = $warehouseItems->map(fn($item) => [
                'ID' => $item->id,
                'Bezeichnung' => $item->bezeichnung,
                'Alte Art.-Nr.' => $item->alte_artikelnummer ?? '-',
                'Neue Art.-Nr.' => $item->neue_artikelnummer,
                'Created At' => $item->created_at,
            ])->toArray();

            $this->table(['ID', 'Bezeichnung', 'Alte Art.-Nr.', 'Neue Art.-Nr.', 'Created At'], $tableData);
        }

        if ($includeOther) {
            $bars = Bar::where('name', 'Krone')->get();
            $drinks = Drink::where('name', 'Mexikaner')->get();
            $pages = Page::where('title', 'Welcome to KleinKram')->get();

            $this->info("Other seeder items found: {$bars->count()} Bar(s), {$drinks->count()} Drink(s), {$pages->count()} Page(s).");
        }

        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No database changes were made.');
            return 0;
        }

        if ($warehouseItems->isEmpty() && (!$includeOther || ($bars->isEmpty() && $drinks->isEmpty() && $pages->isEmpty()))) {
            $this->info('No seeded items were found to remove.');
            return 0;
        }

        if (!$force && !$this->confirm('Are you sure you want to delete these seeded items? Real user items will NOT be affected.', false)) {
            $this->warn('Operation cancelled.');
            return 0;
        }

        $deletedCount = SckWarehouseItem::whereIn('id', $warehouseItems->pluck('id'))->delete();
        $this->info("Successfully deleted {$deletedCount} seeded warehouse item(s).");

        if ($includeOther) {
            if (isset($drinks)) Drink::whereIn('id', $drinks->pluck('id'))->delete();
            if (isset($bars)) Bar::whereIn('id', $bars->pluck('id'))->delete();
            if (isset($pages)) Page::whereIn('id', $pages->pluck('id'))->delete();
            $this->info("Deleted associated test Bar, Drink, and Page records.");
        }

        return 0;
    }
}

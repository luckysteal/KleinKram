<?php

namespace App\Console\Commands;

use App\Models\Sck\SckWarehouseItem;
use App\Models\Bar;
use App\Models\Drink;
use App\Models\Page;
use Illuminate\Console\Command;

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
                            {--force : Force deletion without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely drop seeded dummy items from the database without touching user-created data.';

    /**
     * List of seeded warehouse item old article numbers and designations.
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

        // Find warehouse seeder items
        $warehouseItems = SckWarehouseItem::whereIn('alte_artikelnummer', $this->seededArticleNumbers)
            ->orWhereIn('bezeichnung', $this->seededBezeichnungen)
            ->get();

        $this->info("Found {$warehouseItems->count()} SCK Warehouse seeder item(s).");

        if ($warehouseItems->isNotEmpty()) {
            $tableData = $warehouseItems->map(fn($item) => [
                'ID' => $item->id,
                'Bezeichnung' => $item->bezeichnung,
                'Alte Art.-Nr.' => $item->alte_artikelnummer ?? '-',
                'Neue Art.-Nr.' => $item->neue_artikelnummer,
                'Stückzahl' => $item->stueckzahl,
            ])->toArray();

            $this->table(['ID', 'Bezeichnung', 'Alte Art.-Nr.', 'Neue Art.-Nr.', 'Stückzahl'], $tableData);
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

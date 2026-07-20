<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SckWarehouseItem extends Model
{
    use HasFactory;

    protected $table = 'sck_warehouse_items';

    protected $fillable = [
        'bezeichnung',
        'geraet',
        'artikelgruppe',
        'einheit',
        'steuersatz',
        'lieferant',
        'ek_ohne_st',
        'vk_ohne_st',
        'alte_artikelnummer',
        'neue_artikelnummer',
        'stueckzahl',
        'kommentar',
        'datev_exported',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'ek_ohne_st' => 'decimal:2',
        'vk_ohne_st' => 'decimal:2',
        'stueckzahl' => 'integer',
        'datev_exported' => 'boolean',
    ];

    protected $appends = [
        'is_dienstleistung',
    ];

    public function getIsDienstleistungAttribute(): bool
    {
        return strcasecmp($this->artikelgruppe ?? '', 'Dienstleistung') === 0;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->neue_artikelnummer)) {
                $item->neue_artikelnummer = static::generateUniqueArticleNumber();
            }
        });
    }

    /**
     * Generate a unique 5-digit article number.
     */
    public static function generateUniqueArticleNumber(): string
    {
        do {
            $number = strval(rand(10000, 99999));
        } while (static::where('neue_artikelnummer', $number)->exists());

        return $number;
    }
}

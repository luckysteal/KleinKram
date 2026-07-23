<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SckCustomer extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'tags' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'reputation_reviewed_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(fn (self $customer) => $customer->recordChange('created', null, $customer->auditValues()));
        static::updated(function (self $customer) {
            $before = collect($customer->getOriginal())->only(array_keys($customer->getChanges()))->all();
            $after = collect($customer->getChanges())->except('updated_at')->all();
            if ($after) {
                $customer->recordChange('updated', $before, $after);
            }
        });
        static::deleted(fn (self $customer) => $customer->recordChange('deleted', $customer->auditValues(), null));
        static::restored(fn (self $customer) => $customer->recordChange('restored', null, $customer->auditValues()));
    }

    public function changes() { return $this->hasMany(SckCustomerChange::class, 'customer_id')->latest('created_at'); }
    public function stopTemplates() { return $this->hasMany(SckStopTemplate::class, 'customer_id'); }
    public function tourStops() { return $this->hasMany(SckTourStop::class, 'customer_id'); }
    public function photos() { return $this->hasMany(SckStopPhoto::class, 'customer_id')->latest(); }

    public function getFullAddressAttribute(): string
    {
        return trim(implode(', ', array_filter([
            trim(($this->street ?? '').' '.($this->house_number ?? '')),
            trim(($this->postal_code ?? '').' '.($this->city ?? '')),
            $this->country_code,
        ])));
    }

    private function auditValues(): array
    {
        return $this->only(['datev_account', 'name', 'street', 'house_number', 'postal_code', 'city', 'country_code', 'phone', 'email', 'status', 'tags', 'notes', 'reputation_rating', 'reputation_note']);
    }

    private function recordChange(string $event, ?array $before, ?array $after): void
    {
        $this->changes()->create(['user_id' => auth()->id(), 'event' => $event, 'before' => $before, 'after' => $after]);
    }
}

<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckCustomer;
use App\Models\Sck\SckStopTemplate;
use App\Models\Sck\SckMapPoint;
use App\Services\Sck\TomTomService;
use Illuminate\Http\Request;

class SckAddressSearchController extends Controller
{
    public function __invoke(Request $request, TomTomService $tomTom)
    {
        $data = $request->validate([
            'q' => 'required|string|min:3|max:255',
            'online' => 'nullable|boolean',
        ]);
        $query = trim((string)$data['q']);
        $customers = SckCustomer::where(fn ($q) => $q->where('name', 'like', "%{$query}%")->orWhere('street', 'like', "%{$query}%")->orWhere('city', 'like', "%{$query}%"))->limit(3)->get()->map(fn ($c) => ['source' => 'customer', 'id' => $c->id, 'label' => $c->name.' – '.$c->full_address, 'formatted_address' => $c->full_address, 'customer_id' => $c->id, 'street' => $c->street, 'house_number' => $c->house_number, 'postal_code' => $c->postal_code, 'city' => $c->city, 'country_code' => $c->country_code, 'lat' => $c->latitude, 'lng' => $c->longitude]);
        $stops = SckStopTemplate::where(fn ($q) => $q->where('title', 'like', "%{$query}%")->orWhere('street', 'like', "%{$query}%")->orWhere('city', 'like', "%{$query}%"))->limit(3)->get()->map(fn ($s) => ['source' => 'stop', 'id' => $s->id, 'label' => $s->title.' – '.$s->full_address, 'formatted_address' => $s->full_address, 'customer_id' => $s->customer_id, 'street' => $s->street, 'house_number' => $s->house_number, 'postal_code' => $s->postal_code, 'city' => $s->city, 'country_code' => $s->country_code, 'lat' => $s->latitude, 'lng' => $s->longitude]);
        $points = SckMapPoint::where(fn ($q) => $q->where('name', 'like', "%{$query}%")->orWhere('formatted_address', 'like', "%{$query}%")->orWhere('city', 'like', "%{$query}%"))->limit(3)->get()->map(fn ($p) => ['source' => 'custom_point', 'id' => $p->id, 'label' => $p->name.($p->formatted_address ? ' – '.$p->formatted_address : ''), 'formatted_address' => $p->formatted_address, 'street' => $p->street, 'house_number' => $p->house_number, 'postal_code' => $p->postal_code, 'city' => $p->city, 'country_code' => $p->country_code, 'lat' => $p->latitude, 'lng' => $p->longitude]);
        $local = $points->concat($customers)->concat($stops)->take(6)->values();
        $searchOnline = $request->boolean('online');
        $online = $searchOnline ? collect($tomTom->search($query, 3)) : collect();
        // Keep one concise, relevant result set at a time: local first, online on demand.
        $results = ($searchOnline ? $online : $local)
            ->unique(fn (array $item) => mb_strtolower($item['label']))
            ->take(3)
            ->values();

        return response()->json([
            'local' => $local,
            'online' => $online,
            'results' => $results,
            'online_available' => $tomTom->configured(),
            'searched_online' => $searchOnline,
        ]);
    }
}

{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<gpx version="1.1" creator="SCK Routenplanung" xmlns="http://www.topografix.com/GPX/1/1">
@foreach($tour->stops as $stop)<wpt lat="{{ $stop->latitude }}" lon="{{ $stop->longitude }}"><name>{{ htmlspecialchars($stop->title, ENT_XML1) }}</name><desc>{{ htmlspecialchars($stop->address_snapshot['formatted'] ?? '', ENT_XML1) }}</desc></wpt>@endforeach
@php($points = $tour->routePoints())
@if($points)<trk><name>{{ htmlspecialchars($tour->title, ENT_XML1) }}</name><trkseg>@foreach($points as $point)<trkpt lat="{{ $point['lat'] }}" lon="{{ $point['lng'] }}" />@endforeach</trkseg></trk>@endif
</gpx>

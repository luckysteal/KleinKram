<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckStopPhoto;
use App\Models\Sck\SckTourStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\Sck\PhotoProcessor;
use RuntimeException;

class SckMediaController extends Controller
{
    public function store(Request $request, SckTourStop $stop, PhotoProcessor $processor)
    {
        abort_if($stop->photos()->count() >= config('services.sck_media.max_photos_per_stop', 30), 422, 'Maximale Fotoanzahl für diesen Stopp erreicht.');
        $maxKb = (int) config('services.sck_media.max_upload_mb', 15) * 1024;
        $data = $request->validate(['photo' => "required|file|max:{$maxKb}|mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif", 'caption' => 'nullable|string|max:1000', 'category' => 'required|in:documentation,before,after,damage,receipt', 'customer_id' => 'nullable|exists:sck_customers,id']);
        $file = $request->file('photo');
        try { $processed = $processor->store($file, $stop->id); }
        catch (RuntimeException $e) { return back()->withErrors(['photo' => $e->getMessage()]); }
        $photo = $stop->photos()->create($processed + ['customer_id' => array_key_exists('customer_id', $data) ? $data['customer_id'] : $stop->customer_id, 'user_id' => $request->user()->id, 'original_name' => $file->getClientOriginalName(), 'caption' => $data['caption'] ?? null, 'category' => $data['category']]);
        return back()->with('success', 'Foto wurde sicher gespeichert.');
    }

    public function show(SckStopPhoto $photo)
    {
        abort_unless(Storage::disk('sck_private')->exists($photo->path), 404);
        return Storage::disk('sck_private')->response($photo->path, $photo->original_name, ['Content-Type' => $photo->mime_type, 'Content-Disposition' => 'inline']);
    }

    public function thumbnail(SckStopPhoto $photo)
    {
        $path = $photo->thumbnail_path ?: $photo->path;
        abort_unless(Storage::disk('sck_private')->exists($path), 404);
        return Storage::disk('sck_private')->response($path, $photo->original_name, ['Content-Type' => $photo->mime_type, 'Content-Disposition' => 'inline']);
    }

    public function destroy(SckStopPhoto $photo)
    {
        $photo->delete();
        return back()->with('success', 'Foto liegt 30 Tage im Papierkorb.');
    }

    public function restore(int $photo)
    {
        SckStopPhoto::onlyTrashed()->findOrFail($photo)->restore();
        return back()->with('success', 'Foto wurde wiederhergestellt.');
    }
}

<?php

namespace App\Services\Sck;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PhotoProcessor
{
    public function store(UploadedFile $file, int $stopId): array
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $directory = "stops/{$stopId}";
        $id = (string) Str::uuid();
        $displayPath = "{$directory}/{$id}.webp";
        $thumbPath = "{$directory}/{$id}-thumb.webp";
        $image = $this->decode($file, $mime);
        if (!$image) throw new RuntimeException('Dieses HEIC/HEIF-Bild kann auf diesem Server nicht konvertiert werden. Bitte als JPEG, PNG oder WebP hochladen.');
        $image = $this->normalizeOrientation($image, $file, $mime);

        $display = $this->resize($image, 2560, 2560);
        $thumb = $this->resize($image, 480, 480);
        imagedestroy($image);
        ob_start(); imagewebp($display, null, 84); $displayBytes = ob_get_clean(); imagedestroy($display);
        ob_start(); imagewebp($thumb, null, 78); $thumbBytes = ob_get_clean(); imagedestroy($thumb);
        if (!$displayBytes || !$thumbBytes) throw new RuntimeException('Das Foto konnte nicht verarbeitet werden.');

        Storage::disk('sck_private')->put($displayPath, $displayBytes);
        try { Storage::disk('sck_private')->put($thumbPath, $thumbBytes); }
        catch (\Throwable $e) { Storage::disk('sck_private')->delete($displayPath); throw $e; }
        return ['path' => $displayPath, 'thumbnail_path' => $thumbPath, 'mime_type' => 'image/webp', 'size' => strlen($displayBytes)];
    }

    private function decode(UploadedFile $file, string $mime): mixed
    {
        if (in_array($mime, ['image/heic', 'image/heif'], true) && class_exists(\Imagick::class)) {
            try {
                $imagick = new \Imagick($file->getRealPath()); $imagick->setIteratorIndex(0);
                $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT); $imagick->setImageFormat('png');
                $blob = $imagick->getImageBlob(); $imagick->clear();
                return imagecreatefromstring($blob) ?: null;
            } catch (\Throwable) { return null; }
        }
        if (!function_exists('imagecreatefromstring')) throw new RuntimeException('Die GD-Bilderweiterung fehlt auf dem Server.');
        $bytes = file_get_contents($file->getRealPath());
        return $bytes === false ? null : (imagecreatefromstring($bytes) ?: null);
    }

    private function resize(mixed $source, int $maxWidth, int $maxHeight): mixed
    {
        $width = imagesx($source); $height = imagesy($source);
        $scale = min(1, $maxWidth / max(1, $width), $maxHeight / max(1, $height));
        $newWidth = max(1, (int) round($width * $scale)); $newHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($target, false); imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        return $target;
    }

    private function normalizeOrientation(mixed $image, UploadedFile $file, string $mime): mixed
    {
        if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) return $image;
        try { $orientation = (int) ((exif_read_data($file->getRealPath()) ?: [])['Orientation'] ?? 1); }
        catch (\Throwable) { return $image; }
        $angle = match ($orientation) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
        if (!$angle) return $image;
        $rotated = imagerotate($image, $angle, 0);
        if ($rotated) { imagedestroy($image); return $rotated; }
        return $image;
    }
}

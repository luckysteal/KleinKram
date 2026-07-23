<?php

namespace App\Console\Commands;

use App\Models\Sck\SckComment;
use App\Models\Sck\SckStopPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeSckMediaTrash extends Command
{
    protected $signature = 'sck:purge-media-trash';
    protected $description = 'Permanently remove SCK photos and comments deleted more than 30 days ago';

    public function handle(): int
    {
        SckStopPhoto::onlyTrashed()->where('deleted_at', '<=', now()->subDays(30))->each(function ($photo) {
            Storage::disk('sck_private')->delete(array_filter([$photo->path, $photo->thumbnail_path]));
            $photo->forceDelete();
        });
        SckComment::onlyTrashed()->where('deleted_at', '<=', now()->subDays(30))->forceDelete();
        return self::SUCCESS;
    }
}

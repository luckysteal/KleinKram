<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckComment;
use App\Models\Sck\SckStopPhoto;
use App\Models\Sck\SckTourStop;
use Illuminate\Http\Request;

class SckCommentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['type' => 'required|in:stop,photo', 'id' => 'required|integer', 'body' => 'required|string|max:5000']);
        $target = $data['type'] === 'stop' ? SckTourStop::findOrFail($data['id']) : SckStopPhoto::findOrFail($data['id']);
        $target->comments()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);
        return back()->with('success', 'Kommentar wurde hinzugefügt.');
    }

    public function update(Request $request, SckComment $comment)
    {
        $comment->update(['body' => $request->validate(['body' => 'required|string|max:5000'])['body'], 'edited_at' => now()]);
        return back()->with('success', 'Kommentar wurde aktualisiert.');
    }

    public function destroy(SckComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Kommentar liegt 30 Tage im Papierkorb.');
    }

    public function restore(int $comment)
    {
        SckComment::onlyTrashed()->findOrFail($comment)->restore();
        return back()->with('success', 'Kommentar wurde wiederhergestellt.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BroadcastController extends Controller
{
    public function index()
    {
        // Broadcasts remain stored permanently; this endpoint only exposes the
        // 24-hour window intended for the TV screen.
        return response()->json(
            Broadcast::query()
                ->with(['user:id,name,avatar', 'recipient:id,name,avatar'])
                ->where('created_at', '>=', now()->subDay())
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['good', 'bad'])],
            'content' => ['required', 'string', 'max:200'],
            'recipient_id' => ['nullable', Rule::exists('users', 'id')->where('is_admin', false)],
        ]);

        if ($validated['type'] === 'bad') {
            $validated['recipient_id'] = null;
        }

        $broadcast = Broadcast::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($broadcast->load(['user:id,name,avatar', 'recipient:id,name,avatar']), 201);
    }
}

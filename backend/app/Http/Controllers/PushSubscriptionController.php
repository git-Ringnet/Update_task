<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function publicKey()
    {
        $key = config('webpush.public_key');
        abort_unless(filled($key), 503, 'Web Push chưa được cấu hình trên máy chủ.');

        return response()->json(['public_key' => $key]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:2048',
            'keys.p256dh' => 'required|string|max:512',
            'keys.auth' => 'required|string|max:512',
            'content_encoding' => 'nullable|in:aesgcm,aes128gcm',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'user_id' => auth()->id(),
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['content_encoding'] ?? 'aes128gcm',
            ]
        );

        return response()->json(['message' => 'Đã đăng ký thông báo trình duyệt.'], 201);
    }

    public function destroy(Request $request)
    {
        $request->validate(['endpoint' => 'required|string|max:2048']);

        $query = PushSubscription::where('endpoint', $request->endpoint);
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }
        $query->delete();

        return response()->noContent();
    }
}

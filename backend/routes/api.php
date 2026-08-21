<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\BroadcastController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    $input = trim($request->username);

    // Find by email, name, or email prefix (unaccented username e.g. an, thien, tin, hieu)
    $user = User::where('email', $input)
                ->orWhere('name', $input)
                ->orWhere('email', 'LIKE', $input . '@%')
                ->first();

    if (!$user) {
        return response()->json([
            'message' => 'Tên đăng nhập không tồn tại.'
        ], 422);
    }

    $passOk = Hash::check($request->password, $user->password);

    if (!$passOk) {
        return response()->json([
            'message' => 'Mật khẩu không chính xác.'
        ], 422);
    }

    // Auto-update password to Ringnet@123 if needed
    if ($request->password === 'Ringnet@123' && !Hash::check('Ringnet@123', $user->password)) {
        $user->password = Hash::make('Ringnet@123');
    }

    $user->api_token = Str::random(60);
    $user->api_token_expires_at = now()->addHours(24);
    $user->save();

    return response()->json([
        'user' => $user,
        'token' => $user->api_token
    ]);
});

Route::post('/google-login-real', function (Request $request) {
    $request->validate([
        'id_token' => 'required|string',
    ]);

    $idToken = $request->id_token;

    // Verify token validity with Google (bypass local Windows cURL SSL verification)
    $response = Http::withoutVerifying()->get("https://oauth2.googleapis.com/tokeninfo", [
        'id_token' => $idToken,
    ]);

    if ($response->failed()) {
        return response()->json([
            'message' => 'Mã xác thực Google không hợp lệ hoặc đã hết hạn.'
        ], 422);
    }

    $payload = $response->json();

    // Verify client ID (aud)
    $clientId = env('GOOGLE_CLIENT_ID', '490106347668-6odqmcnvrkq8g6opuhu0idaf6joesdn6.apps.googleusercontent.com');
    if (($payload['aud'] ?? '') !== $clientId) {
        return response()->json([
            'message' => 'Client ID Google không khớp.'
        ], 422);
    }

    $email = $payload['email'];
    $name = $payload['name'] ?? $email;
    $avatar = $payload['picture'] ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120';

    // Only administrators can create accounts. Google login may only use an existing one.
    $user = User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['message' => 'Tài khoản chưa được quản trị viên cấp quyền.'], 403);
    }

    // Update avatar if changed
    if ($avatar && $user->avatar !== $avatar) {
        $user->avatar = $avatar;
    }

    // Refresh local token session
    $user->api_token = Str::random(60);
    $user->api_token_expires_at = now()->addHours(24);
    $user->save();

    return response()->json([
        'user' => $user,
        'token' => $user->api_token
    ]);
});

Route::get('/active-users', function () {
    return response()->json(User::where('is_admin', false)
        ->select('id', 'name', 'email', 'avatar')->get()->map(function($user) {
        $username = explode('@', $user->email)[0];
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $username,
            'email' => $user->email,
            'avatar' => $user->avatar
        ];
    }));
});

// TV only receives recent broadcasts. Older records stay in the database.
Route::get('/tv/broadcasts', [BroadcastController::class, 'index']);

// Protected Group
Route::middleware('auth.token')->group(function () {
    Route::get('/me', function (Request $request) {
        return response()->json(auth()->user());
    });

    Route::put('/me', function (Request $request) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'avatar' => 'sometimes|nullable|string',
            'view_mode' => 'sometimes|nullable|string|in:list,grouped,notes',
            'pinned_customers' => 'sometimes|nullable|array',
        ]);
        $user->update($validated);
        return response()->json($user);
    });

    Route::post('/logout', function (Request $request) {
        $user = auth()->user();
        if ($user) {
            $user->api_token = null;
            $user->api_token_expires_at = null;
            $user->save();
        }
        return response()->json(['message' => 'Đăng xuất thành công']);
    });

    Route::post('/broadcasts', [BroadcastController::class, 'store']);

    Route::get('/users', function () {
        return response()->json(User::where('is_admin', false)
            ->select('id', 'name', 'email', 'avatar')
            ->withCount('ledProjects')
            ->get());
    });

    Route::post('/users', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar' => $validated['avatar'] ?? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=120',
        ]);

        return response()->json($user, 201);
    })->middleware('admin');

    Route::delete('/users/{id}', function ($id) {
        $currentUser = auth()->user();
        if ($currentUser->id == $id) {
            return response()->json(['message' => 'Bạn không thể tự xóa chính mình khỏi hệ thống.'], 400);
        }

        $user = User::where('is_admin', false)->find($id);
        if (!$user) {
            return response()->json(['message' => 'Thành viên không tồn tại.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Xóa thành viên thành công.']);
    })->middleware('admin');

    // Projects
    Route::put('/projects/bulk', [ProjectController::class, 'bulkUpdate']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::post('/projects/reorder', [ProjectController::class, 'reorder']);
    Route::get('/projects/{id}/access', [ProjectController::class, 'access']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::patch('/projects/{id}/health', [ProjectController::class, 'updateHealth']);
    Route::patch('/projects/{id}/pin', [ProjectController::class, 'togglePin']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // Milestones
    Route::get('/projects/{id}/milestones', [\App\Http\Controllers\MilestoneController::class, 'index']);
    Route::post('/projects/{id}/milestones', [\App\Http\Controllers\MilestoneController::class, 'store']);
    Route::put('/milestones/{id}', [\App\Http\Controllers\MilestoneController::class, 'update']);
    Route::delete('/milestones/{id}', [\App\Http\Controllers\MilestoneController::class, 'destroy']);

    // Customers
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // Comments
    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
});

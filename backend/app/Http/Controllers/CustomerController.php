<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('projects')
            ->with('updater')
            ->orderBy('last_activity_at', 'desc');

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $customers = $query->get();

        $counts = [
            'all' => Customer::count(),
            'customer' => Customer::where('type', 'customer')->count(),
            'vendor' => Customer::where('type', 'vendor')->count(),
            'internal' => Customer::where('type', 'internal')->count(),
            'other' => Customer::where('type', 'other')->count(),
        ];

        return response()->json([
            'customers' => $customers,
            'counts' => $counts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:customer,vendor,internal,other',
            'status' => 'required|in:green,yellow,red',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = auth()->id() ?? $request->user_id ?? \App\Models\User::first()->id ?? null;
        $validated['last_activity_at'] = Carbon::now();

        $customer = Customer::create($validated);
        $customer->load('updater');

        return response()->json($customer, 201);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'sometimes|in:customer,vendor,internal,other',
            'status' => 'sometimes|in:green,yellow,red',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = auth()->id() ?? $request->user_id ?? \App\Models\User::first()->id ?? null;
        $validated['last_activity_at'] = Carbon::now();

        $customer->update($validated);
        $customer->load('updater');

        return response()->json($customer);
    }

    public function show($id)
    {
        $customer = Customer::with(['projects.lead', 'updater'])->findOrFail($id);
        return response()->json($customer);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['message' => 'Đã xóa mối quan hệ']);
    }
}

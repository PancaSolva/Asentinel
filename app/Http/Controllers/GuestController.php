<?php

namespace App\Http\Controllers;

use App\Models\WebGuest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GuestController extends Controller
{
    public function addGuestAccess(Request $request): JsonResponse
    {
        // Auth check
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'id' => 'required|exists:users,id',
            'id_aplikasi' => 'required|exists:aplikasi,id_aplikasi',
        ]);

        Log::info('Guest add request', $validated);

        try {
            $guest = WebGuest::firstOrCreate(
                ['id' => $validated['id'], 'id_aplikasi' => $validated['id_aplikasi']]
            );

            if (!$guest->wasRecentlyCreated) {
                return response()->json(['error' => 'Guest access already exists'], 409);
            }

            $guest->load(['user', 'aplikasi']);

            Log::info('Guest created successfully', ['premission_id' => $guest->premission_id]);
            
            Cache::forget('guest_list');

            return response()->json([
                'success' => true,
                'data' => $guest
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error creating guest', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Database error'], 500);
        } catch (\Exception $e) {
            Log::error('Error creating guest', [
                'validated' => $validated,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to create guest'], 500);
        }
    }

    public function removeGuestAccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|exists:users,id',
            'id_aplikasi' => 'required|exists:aplikasi,id_aplikasi',
        ]);

        $deleted = WebGuest::where('id', $validated['id'])
            ->where('id_aplikasi', $validated['id_aplikasi'])
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        Cache::forget('guest_list');

        return response()->json([
            'success' => true,
            'message' => 'Guest access removed successfully'
        ]);
    }

    public function guestAccessList(): JsonResponse
    {
        $guests = Cache::remember('guest_list', 300, function () {
            return WebGuest::with(['user', 'aplikasi'])->get();
        });

        return response()->json($guests);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\WebGuest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'id_aplikasi' => 'required|exists:aplikasi,id',
        ]);

        Log::info('Guest add request', $validated);

        $exists = WebGuest::where('id', $validated['id'])
            ->where('id_aplikasi', $validated['id_aplikasi'])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Guest access already exists'], 409);
        }

        try {
            $guest = \DB::transaction(function () use ($validated) {
                $guest = WebGuest::create($validated);
                return $guest->load(['user', 'aplikasi']);
            });

            Log::info('Guest created successfully', ['premission_id' => $guest->premission_id]);
            
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
            'id_aplikasi' => 'required|exists:aplikasi,id',
        ]);

        $deleted = WebGuest::where('id', $validated['id'])
            ->where('id_aplikasi', $validated['id_aplikasi'])
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest access removed successfully'
        ]);
    }

    public function guestAccessList(): JsonResponse
    {
        
        $guests = WebGuest::with(['user', 'aplikasi'])->get();

        return response()->json($guests);
    }
}
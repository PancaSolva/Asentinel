<?php

namespace App\Http\Controllers;

use App\Models\WebGuest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuestController extends Controller
{
    public function addGuestAccess(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_aplikasi' => 'required|exists:aplikasi,id',
            'id_service' => 'nullable|exists:services,id_service',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['id'] = $id;

        // Check if already exists
        $exists = WebGuest::where('id', $id)
            ->where(function ($query) use ($data) {
                $query->where('id_aplikasi', $data['id_aplikasi'])
                      ->orWhere(function ($q) use ($data) {
                          if (isset($data['id_service'])) {
                              $q->where('id_service', $data['id_service']);
                          }
                      });
            })->exists();

        if ($exists) {
            return response()->json(['error' => 'Guest access already exists'], 409);
        }

        $guest = WebGuest::create($data);
        $guest->load(['user', 'aplikasi', 'service']);

        return response()->json([
            'success' => true,
            'data' => $guest
        ]);
    }

    public function removeGuestAccess(Request $request, $id): JsonResponse
    {
        $deleted = WebGuest::where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guest access removed successfully',
            'deleted_count' => $deleted
        ]);
    }

    public function guestAccessList(Request $request): JsonResponse
    {
        $guests = WebGuest::all();

        return response()->json($guests);
    }
}

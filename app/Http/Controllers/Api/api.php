<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class api extends Controller
{
    /**
     * Display a listing of pins.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Pin::with(['user', 'aplikasi'])->get()
        ]);
    }

    /**
     * Store a newly created pin in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id',
            'id_aplikasi' => 'required|exists:aplikasi,id_aplikasi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if pin already exists
        $exists = Pin::where('id_user', $request->id_user)
                    ->where('id_aplikasi', $request->id_aplikasi)
                    ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This application is already pinned for this user.'
            ], 409);
        }

        $pin = Pin::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pin created successfully',
            'data' => $pin
        ], 201);
    }

    /**
     * Display the specified pin.
     */
    public function show($id)
    {
        $pin = Pin::with(['user', 'aplikasi'])->find($id);

        if (!$pin) {
            return response()->json([
                'success' => false,
                'message' => 'Pin not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pin
        ]);
    }

    /**
     * Update the specified pin in storage.
     */
    public function update(Request $request, $id)
    {
        $pin = Pin::find($id);

        if (!$pin) {
            return response()->json([
                'success' => false,
                'message' => 'Pin not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_user' => 'sometimes|required|exists:users,id',
            'id_aplikasi' => 'sometimes|required|exists:aplikasi,id_aplikasi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pin->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pin updated successfully',
            'data' => $pin
        ]);
    }

    /**
     * Remove the specified pin from storage.
     */
    public function destroy($id)
    {
        $pin = Pin::find($id);

        if (!$pin) {
            return response()->json([
                'success' => false,
                'message' => 'Pin not found'
            ], 404);
        }

        $pin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pin deleted successfully'
        ]);
    }
}

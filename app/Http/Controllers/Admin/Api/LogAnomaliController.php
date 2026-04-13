<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\LogAnomali;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogAnomaliController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => LogAnomali::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_aplikasi' => 'required|exists:aplikasi,id_aplikasi',
            'id_service' => 'required|exists:services,id_service',
            'description' => 'nullable|string',
            'severity' => 'nullable|string|max:255',
            'detected_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $log = LogAnomali::create($validator->validated());

        return response()->json(['success' => true, 'message' => 'Log anomali created', 'data' => $log], 201);
    }

    public function show($id)
    {
        $log = LogAnomali::find($id);
        if (!$log) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return response()->json(['success' => true, 'data' => $log]);
    }

    public function update(Request $request, $id)
    {
        $log = LogAnomali::find($id);
        if (!$log) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'id_aplikasi' => 'sometimes|required|exists:aplikasi,id_aplikasi',
            'id_service' => 'sometimes|required|exists:services,id_service',
            'description' => 'nullable|string',
            'severity' => 'nullable|string|max:255',
            'detected_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $log->update($validator->validated());

        return response()->json(['success' => true, 'message' => 'Log anomali updated', 'data' => $log]);
    }

    public function destroy($id)
    {
        $log = LogAnomali::find($id);
        if (!$log) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $log->delete();

        return response()->json(['success' => true, 'message' => 'Log anomali deleted']);
    }
}

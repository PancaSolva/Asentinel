<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\LogMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogMonitorController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => LogMonitor::with(['aplikasi', 'service'])
                ->latest('checked_at')
                ->paginate(50)
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_aplikasi' => 'required|exists:aplikasi,id_aplikasi',
            'id_service' => 'required|exists:services,id_service',
            'url' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'http_status_code' => 'nullable|integer',
            'response_time_ms' => 'nullable|integer',
            'checked_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $log = LogMonitor::create($validator->validated());

        return response()->json(['success' => true, 'message' => 'Log monitor created', 'data' => $log], 201);
    }

    public function show($id)
    {
        $log = LogMonitor::find($id);
        if (!$log) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return response()->json(['success' => true, 'data' => $log]);
    }

    public function update(Request $request, $id)
    {
        $log = LogMonitor::find($id);
        if (!$log) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'id_aplikasi' => 'sometimes|required|exists:aplikasi,id_aplikasi',
            'id_service' => 'sometimes|required|exists:services,id_service',
            'url' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'http_status_code' => 'nullable|integer',
            'response_time_ms' => 'nullable|integer',
            'checked_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $log->update($validator->validated());

        return response()->json(['success' => true, 'message' => 'Log monitor updated', 'data' => $log]);
    }

    public function destroy($id)
    {
        $log = LogMonitor::find($id);
        if (!$log) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $log->delete();

        return response()->json(['success' => true, 'message' => 'Log monitor deleted']);
    }
}

<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Service::with('aplikasi')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_aplikasi' => 'required|exists:aplikasi,id_aplikasi',
            'nama' => 'required|string|max:255',
            'tipe_service' => 'nullable|string|max:255',
            'ip_local' => 'nullable|string|max:255',
            'url_service' => 'nullable|string|max:255',
            'url_repository' => 'nullable|string|max:255',
            'url_api_docs' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $service = Service::create($validator->validated());

        return response()->json(['success' => true, 'message' => 'Service created', 'data' => $service], 201);
    }

    public function show($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return response()->json(['success' => true, 'data' => $service]);
    }

    public function update(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'id_aplikasi' => 'sometimes|required|exists:aplikasi,id_aplikasi',
            'nama' => 'sometimes|required|string|max:255',
            'tipe_service' => 'nullable|string|max:255',
            'ip_local' => 'nullable|string|max:255',
            'url_service' => 'nullable|string|max:255',
            'url_repository' => 'nullable|string|max:255',
            'url_api_docs' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $service->update($validator->validated());

        return response()->json(['success' => true, 'message' => 'Service updated', 'data' => $service]);
    }

    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $service->delete();

        return response()->json(['success' => true, 'message' => 'Service deleted']);
    }
}

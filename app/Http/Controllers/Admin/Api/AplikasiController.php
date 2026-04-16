<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AplikasiController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Aplikasi::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe' => 'nullable|string|max:255',
            'ip_local' => 'nullable|string|max:255',
            'url_service' => 'nullable|string|max:255',
            'url_repository' => 'nullable|string|max:255',
            'url_api_docs' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $aplikasi = Aplikasi::create($validator->validated());

        return response()->json(['success' => true, 'message' => 'Aplikasi created', 'data' => $aplikasi], 201);
    }

    public function show($id)
    {
        $aplikasi = Aplikasi::with(['services', 'logMonitors' => function($query) {
            $query->latest()->limit(10);
        }])->find($id);
        
        if (!$aplikasi) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return response()->json(['success' => true, 'data' => $aplikasi]);
    }

    public function update(Request $request, $id)
    {
        $aplikasi = Aplikasi::find($id);
        if (!$aplikasi) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tipe' => 'nullable|string|max:255',
            'ip_local' => 'nullable|string|max:255',
            'url_service' => 'nullable|string|max:255',
            'url_repository' => 'nullable|string|max:255',
            'url_api_docs' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $aplikasi->update($validator->validated());

        return response()->json(['success' => true, 'message' => 'Aplikasi updated', 'data' => $aplikasi]);
    }

    public function destroy($id)
    {
        $aplikasi = Aplikasi::find($id);
        if (!$aplikasi) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $aplikasi->delete();

        return response()->json(['success' => true, 'message' => 'Aplikasi deleted']);
    }
}

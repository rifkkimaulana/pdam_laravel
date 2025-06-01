<?php

namespace App\Http\Controllers;

use App\Models\Penggunaan;
use Illuminate\Http\Request;

class PenggunaanController extends Controller
{
    // Daftar data penggunaan berdasarkan pelanggan_id (filter optional)
    public function index(Request $request)
    {
        $pelanggan_id = $request->query('pelanggan_id');
        $query = Penggunaan::query();

        if ($pelanggan_id) {
            $query->where('pelanggan_id', $pelanggan_id);
        }

        $data = $query->orderBy('tanggal_catat', 'desc')->get();

        return response()->json($data);
    }

    // Simpan data penggunaan baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelanggan_id' => 'required|integer|exists:tb_user,id',
            'tanggal_catat' => 'required|date',
            'angka_meter' => 'required|integer',
            'foto_meter' => 'nullable|string',
            'dicatat_oleh' => 'required|integer|exists:tb_user,id',
            'catatan' => 'nullable|string'
        ]);

        $penggunaan = Penggunaan::create($validated);

        return response()->json([
            'message' => 'Data penggunaan berhasil ditambahkan',
            'data' => $penggunaan
        ], 201);
    }

    // Detail data penggunaan
    public function show($id)
    {
        $penggunaan = Penggunaan::find($id);
        if (!$penggunaan) {
            return response()->json(['message' => 'Data penggunaan tidak ditemukan'], 404);
        }
        return response()->json($penggunaan);
    }

    // Update data penggunaan
    public function update(Request $request, $id)
    {
        $penggunaan = Penggunaan::find($id);
        if (!$penggunaan) {
            return response()->json(['message' => 'Data penggunaan tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'pelanggan_id' => 'sometimes|integer|exists:tb_user,id',
            'tanggal_catat' => 'sometimes|date',
            'angka_meter' => 'sometimes|integer',
            'foto_meter' => 'nullable|string',
            'dicatat_oleh' => 'sometimes|integer|exists:tb_user,id',
            'catatan' => 'nullable|string'
        ]);

        $penggunaan->update($validated);

        return response()->json([
            'message' => 'Data penggunaan berhasil diperbarui',
            'data' => $penggunaan
        ]);
    }

    // Hapus data penggunaan
    public function destroy($id)
    {
        $penggunaan = Penggunaan::find($id);
        if (!$penggunaan) {
            return response()->json(['message' => 'Data penggunaan tidak ditemukan'], 404);
        }
        $penggunaan->delete();

        return response()->json(['message' => 'Data penggunaan berhasil dihapus']);
    }
}

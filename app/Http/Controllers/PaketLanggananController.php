<?php

namespace App\Http\Controllers;

use App\Models\PaketLangganan;
use Illuminate\Http\Request;

class PaketLanggananController extends Controller
{
    // Daftar paket langganan
    public function index()
    {
        $paket = PaketLangganan::all();
        return response()->json($paket);
    }

    // Tambah paket langganan
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_paket'   => 'required|string|max:255',
            'harga_paket'  => 'required|numeric',
            'masa_aktif'   => 'required|integer',
            'satuan'       => 'required|in:hari,bulan',
            'deskripsi'    => 'nullable|string',
            'status'       => 'boolean',
        ]);

        $paket = PaketLangganan::create($validated);
        return response()->json(['message' => 'Paket berhasil ditambahkan', 'data' => $paket], 201);
    }

    // Ubah paket langganan
    public function update(Request $request, $id)
    {
        $paket = PaketLangganan::findOrFail($id);

        $validated = $request->validate([
            'nama_paket'   => 'required|string|max:255',
            'harga_paket'  => 'required|numeric',
            'masa_aktif'   => 'required|integer',
            'satuan'       => 'required|in:hari,bulan',
            'deskripsi'    => 'nullable|string',
            'status'       => 'boolean',
        ]);

        $paket->update($validated);
        return response()->json(['message' => 'Paket berhasil diperbarui', 'data' => $paket]);
    }

    // Detail paket langganan
    public function show($id)
    {
        $paket = PaketLangganan::find($id);

        if (!$paket) {
            return response()->json([
                'message' => 'Paket tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $paket
        ]);
    }


    // Hapus paket langganan
    public function destroy($id)
    {
        $paket = PaketLangganan::findOrFail($id);
        $paket->delete();

        return response()->json(['message' => 'Paket berhasil dihapus']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketLangganan;

class PaketLanggananController extends Controller
{
    // GET semua paket langganan
    public function index()
    {
        return response()->json(PaketLangganan::orderBy('nama_paket', 'asc')->get());
    }

    // GET paket langganan berdasarkan ID
    public function show($id)
    {
        return response()->json(PaketLangganan::findOrFail($id));
    }

    // POST tambah paket langganan baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|max:50',
            'harga_paket' => 'required|numeric|min:0',
            'masa_aktif' => 'required|integer|min:1',
            'satuan' => 'required|in:hari,bulan',
            'deskripsi' => 'nullable'
        ]);

        $paket = PaketLangganan::create($request->all());
        return response()->json([
            'message' => 'Paket langganan berhasil ditambahkan!',
            'paket' => $paket
        ], 201);
    }

    // PUT update paket langganan
    public function update(Request $request, $id)
    {
        $paket = PaketLangganan::findOrFail($id);

        $request->validate([
            'nama_paket' => 'sometimes|required|max:50',
            'harga_paket' => 'sometimes|required|numeric|min:0',
            'masa_aktif' => 'sometimes|required|integer|min:1',
            'satuan' => 'sometimes|required|in:hari,bulan',
            'deskripsi' => 'nullable'
        ]);

        $paket->update($request->all());
        return response()->json([
            'message' => 'Paket langganan berhasil diperbarui!',
            'paket' => $paket
        ]);
    }

    // DELETE paket langganan
    public function destroy($id)
    {
        PaketLangganan::destroy($id);
        return response()->json(['message' => 'Paket langganan berhasil dihapus']);
    }
}

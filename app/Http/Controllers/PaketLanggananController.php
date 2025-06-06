<?php

namespace App\Http\Controllers;

use App\Models\PaketLangganan;
use Illuminate\Http\Request;

class PaketLanggananController extends Controller
{
    public function index()
    {
        $paket = PaketLangganan::latest()->get();
        return response()->json($paket);
    }

    private function validatePaketLangganan(Request $request)
    {
        return $request->validate([
            'nama_paket'   => 'required|string|max:255',
            'harga_paket'  => 'required|numeric',
            'masa_aktif'   => 'required|integer',
            'satuan'       => 'required|in:hari,bulan',
            'deskripsi'    => 'nullable|string',
            'status'       => 'boolean',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePaketLangganan($request);
        $paket = PaketLangganan::create($validated);
        return response()->json(['message' => 'Paket berhasil ditambahkan', 'data' => $paket], 201);
    }

    public function update(Request $request, $id)
    {
        $paket = PaketLangganan::findOrFail($id);
        $validated = $this->validatePaketLangganan($request);
        $paket->update($validated);
        return response()->json(['message' => 'Paket berhasil diperbarui', 'data' => $paket]);
    }

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

    public function destroy($id)
    {
        $paket = PaketLangganan::findOrFail($id);
        $paket->delete();

        return response()->json(['message' => 'Paket berhasil dihapus']);
    }
}

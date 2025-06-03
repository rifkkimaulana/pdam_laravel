<?php

namespace App\Http\Controllers;

use App\Models\BlokTarif;
use Illuminate\Http\Request;

class BlokTarifController extends Controller
{
    // GET /blok-tarif
    public function index(Request $request)
    {
        // Jika ada paket_id di query, filter berdasarkan paket_id
        $paketId = $request->query('paket_id');
        $query = BlokTarif::query();
        if ($paketId) {
            $query->where('paket_id', $paketId); // sesuaikan kolom ke 'paket_id'
        }
        return response()->json($query->get());
    }

    // GET /blok-tarif/{id}
    public function show($id)
    {
        $blok = BlokTarif::find($id);
        if (!$blok) {
            return response()->json(['message' => 'Blok tarif tidak ditemukan'], 404);
        }
        return response()->json($blok);
    }

    // POST /blok-tarif
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paket_pengguna_id' => 'required|integer',
            'nama_blok' => 'required|string|max:255',
            'range_awal' => 'required|integer',
            'range_akhir' => 'required|integer',
            'harga' => 'required|numeric',
        ]);
        $blok = BlokTarif::create($validated);
        return response()->json($blok, 201);
    }

    // PUT /blok-tarif/{id}
    public function update(Request $request, $id)
    {
        $blok = BlokTarif::find($id);
        if (!$blok) {
            return response()->json(['message' => 'Blok tarif tidak ditemukan'], 404);
        }
        $validated = $request->validate([
            'paket_pengguna_id' => 'required|integer',
            'nama_blok' => 'required|string|max:255',
            'range_awal' => 'required|integer',
            'range_akhir' => 'required|integer',
            'harga' => 'required|numeric',
        ]);
        $blok->update($validated);
        return response()->json($blok);
    }

    // DELETE /blok-tarif/{id}
    public function destroy($id)
    {
        $blok = BlokTarif::find($id);
        if (!$blok) {
            return response()->json(['message' => 'Blok tarif tidak ditemukan'], 404);
        }
        $blok->delete();
        return response()->json(['message' => 'Blok tarif berhasil dihapus']);
    }
}

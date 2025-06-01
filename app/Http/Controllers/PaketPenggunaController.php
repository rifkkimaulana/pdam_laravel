<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaketPengguna;
use App\Models\BlokTarif;

class PaketPenggunaController extends Controller
{
    // Daftar paket lengkap dengan blok tarif
    public function index()
    {
        $paket = PaketPengguna::with('blokTarif')->get();
        return response()->json($paket, 200);
    }

    // Tambah paket baru sekaligus banyak blok tarif
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengelola_id' => 'required|integer',
            'nama_paket' => 'required|string|max:50',
            'biaya_admin' => 'required|numeric',
            'deskripsi' => 'required|string',
            'status' => 'required|in:enable,disable',
            'blok_tarif' => 'required|array|min:1',
            'blok_tarif.*.batas_atas' => 'required|integer|min:1',
            'blok_tarif.*.harga_per_m3' => 'required|numeric|min:0',
        ]);

        // Simpan paket
        $paket = PaketPengguna::create($validated);

        // Simpan blok tarif, blok_ke diisi otomatis urut
        foreach ($validated['blok_tarif'] as $index => $blok) {
            BlokTarif::create([
                'paket_pengguna_id' => $paket->id,
                'blok_ke' => $index + 1,
                'batas_atas' => $blok['batas_atas'],
                'harga_per_m3' => $blok['harga_per_m3'],
            ]);
        }

        return response()->json([
            'message' => 'Paket berhasil dibuat',
            'paket' => $paket->load('blokTarif'),
        ], 201);
    }

    // Detail paket beserta blok tarif
    public function show($id)
    {
        $paket = PaketPengguna::with('blokTarif')->find($id);
        if (!$paket) {
            return response()->json(['message' => 'Paket tidak ditemukan'], 404);
        }
        return response()->json($paket, 200);
    }

    // Update paket + blok tarif (blok dihapus dulu lalu diupdate ulang)
    public function update(Request $request, $id)
    {
        $paket = PaketPengguna::find($id);
        if (!$paket) {
            return response()->json(['message' => 'Paket tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'pengelola_id' => 'required|integer',
            'nama_paket' => 'required|string|max:50',
            'biaya_admin' => 'required|numeric',
            'deskripsi' => 'required|string',
            'status' => 'required|in:enable,disable',
            'blok_tarif' => 'required|array|min:1',
            'blok_tarif.*.batas_atas' => 'required|integer|min:1',
            'blok_tarif.*.harga_per_m3' => 'required|numeric|min:0',
        ]);

        $paket->update($validated);

        // Hapus blok lama
        $paket->blokTarif()->delete();

        // Simpan blok baru
        foreach ($validated['blok_tarif'] as $index => $blok) {
            BlokTarif::create([
                'paket_pengguna_id' => $paket->id,
                'blok_ke' => $index + 1,
                'batas_atas' => $blok['batas_atas'],
                'harga_per_m3' => $blok['harga_per_m3'],
            ]);
        }

        return response()->json([
            'message' => 'Paket berhasil diperbarui',
            'paket' => $paket->load('blokTarif'),
        ], 200);
    }

    // Hapus paket dan blok tarif terkait
    public function destroy($id)
    {
        $paket = PaketPengguna::find($id);
        if (!$paket) {
            return response()->json(['message' => 'Paket tidak ditemukan'], 404);
        }

        $paket->blokTarif()->delete();
        $paket->delete();

        return response()->json(['message' => 'Paket berhasil dihapus'], 200);
    }
}

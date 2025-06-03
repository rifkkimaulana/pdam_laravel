<?php

namespace App\Http\Controllers;

use App\Models\PaketPengguna;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    // GET /paket
    public function index(Request $request)
    {
        $pengelolaId = $request->query('pengelola_id');
        $query = PaketPengguna::select('id', 'nama_paket', 'pengelola_id');
        if ($pengelolaId) {
            $query->where('pengelola_id', $pengelolaId);
        }
        return response()->json($query->get());
    }

    // GET /paket/{paket}/blok-tarif
    public function blokTarif($paketId)
    {
        // Ambil blok tarif berdasarkan paket_id
        $paket = PaketPengguna::with('blokTarif')->find($paketId);
        if (!$paket) {
            return response()->json([]);
        }
        // Asumsikan relasi blokTarif sudah ada di model PaketPengguna
        return response()->json($paket->blokTarif);
    }
}

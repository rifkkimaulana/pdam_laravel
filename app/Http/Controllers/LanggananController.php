<?php

namespace App\Http\Controllers;

use App\Models\Langganan;
use Illuminate\Http\Request;

class LanggananController extends Controller
{
    // Menampilkan semua data langganan
    public function index()
    {
        $data = Langganan::with(['user', 'pengelola', 'paket'])->get();
        return response()->json($data);
    }

    // Menambahkan data langganan baru
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:tb_user,id',
            'pengelola_id' => 'required|exists:tb_pengelola,id',
            'paket_id' => 'required|exists:tb_paket_langganan,id',
            'mulai_langganan' => 'required|date',
            'akhir_langganan' => 'required|date|after:mulai_langganan',
        ]);

        $langganan = Langganan::create([
            'user_id' => $request->user_id,
            'pengelola_id' => $request->pengelola_id,
            'paket_id' => $request->paket_id,
            'mulai_langganan' => $request->mulai_langganan,
            'akhir_langganan' => $request->akhir_langganan,
            'status' => 'Tidak Aktif',
        ]);

        return response()->json($langganan, 201);
    }

    // Menampilkan detail langganan
    public function show($id)
    {
        $langganan = Langganan::with(['user', 'pengelola', 'paket'])->findOrFail($id);
        return response()->json($langganan);
    }

    // Update data langganan
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:tb_user,id',
            'pengelola_id' => 'required|exists:tb_pengelola,id',
            'paket_id' => 'required|exists:tb_paket_langganan,id',
            'mulai_langganan' => 'required|date',
            'akhir_langganan' => 'required|date|after:mulai_langganan',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        $langganan = Langganan::findOrFail($id);
        $langganan->update($request->all());

        return response()->json($langganan);
    }

    // Hapus data langganan
    public function destroy($id)
    {
        $langganan = Langganan::findOrFail($id);
        $langganan->delete();

        return response()->json(['message' => 'Langganan berhasil dihapus']);
    }
}

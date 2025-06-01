<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function index()
    {
        return Tagihan::with('pelanggan')->latest('periode')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => 'required|exists:tb_pelanggan,id',
            'periode' => 'required|date',
            'jumlah_tagihan' => 'required|numeric',
            'denda' => 'nullable|numeric',
            'tanggal_jatuh_tempo' => 'required|date',
            'status' => 'required|in:Lunas,Belum Bayar,Terlambat',
            'keterangan_potongan' => 'nullable|string',
            'jumlah_potongan' => 'nullable|numeric',
        ]);

        $tagihan = Tagihan::create($data);

        return response()->json(['message' => 'Tagihan berhasil dibuat', 'data' => $tagihan], 201);
    }

    public function update(Request $request, $id)
    {
        $tagihan = Tagihan::findOrFail($id);

        $data = $request->validate([
            'periode' => 'sometimes|date',
            'jumlah_tagihan' => 'sometimes|numeric',
            'denda' => 'nullable|numeric',
            'tanggal_jatuh_tempo' => 'sometimes|date',
            'status' => 'required|in:Lunas,Belum Bayar,Terlambat',
            'keterangan_potongan' => 'nullable|string',
            'jumlah_potongan' => 'nullable|numeric',
        ]);

        $tagihan->update($data);

        return response()->json(['message' => 'Tagihan diperbarui', 'data' => $tagihan]);
    }

    public function destroy($id)
    {
        $tagihan = Tagihan::findOrFail($id);
        $tagihan->pembayaran()->delete();
        $tagihan->delete();

        return response()->json(['message' => 'Tagihan dan pembayaran terkait berhasil dihapus']);
    }
}

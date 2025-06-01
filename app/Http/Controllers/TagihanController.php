<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TagihanPelanggan;

class TagihanController extends Controller
{
    /**
     * Menampilkan daftar seluruh tagihan pelanggan.
     */
    public function index()
    {
        // Mengurutkan tagihan berdasarkan tanggal periode tertua ke terbaru (atau sebaliknya)
        $tagihan = TagihanPelanggan::orderBy('periode', 'desc')->get();
        return response()->json($tagihan);
    }

    /**
     * Menyimpan tagihan pelanggan baru ke dalam database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'pelanggan_id'         => 'required|exists:tb_pelanggan,id',
            'periode'              => 'required|date',
            'jumlah_tagihan'       => 'required|numeric|min:0',
            'denda'                => 'nullable|numeric|min:0',
            'tanggal_jatuh_tempo'  => 'required|date',
            'status'               => 'required|in:belum_bayar,lunas,terlambat'
        ]);

        $tagihan = TagihanPelanggan::create($validatedData);

        return response()->json([
            'message' => 'Tagihan pelanggan berhasil dibuat',
            'tagihan' => $tagihan
        ], 201);
    }

    /**
     * Menampilkan detail tagihan berdasarkan ID.
     */
    public function show($id)
    {
        $tagihan = TagihanPelanggan::findOrFail($id);
        return response()->json($tagihan);
    }

    /**
     * Mengubah data tagihan pelanggan.
     */
    public function update(Request $request, $id)
    {
        $tagihan = TagihanPelanggan::findOrFail($id);

        $validatedData = $request->validate([
            'pelanggan_id'         => 'sometimes|required|exists:tb_pelanggan,id',
            'periode'              => 'sometimes|required|date',
            'jumlah_tagihan'       => 'sometimes|required|numeric|min:0',
            'denda'                => 'nullable|numeric|min:0',
            'tanggal_jatuh_tempo'  => 'sometimes|required|date',
            'status'               => 'sometimes|required|in:belum_bayar,lunas,terlambat'
        ]);

        $tagihan->update($validatedData);

        return response()->json([
            'message' => 'Tagihan pelanggan berhasil diperbarui',
            'tagihan' => $tagihan
        ]);
    }

    /**
     * Menghapus tagihan pelanggan dari database.
     */
    public function destroy($id)
    {
        TagihanPelanggan::destroy($id);
        return response()->json([
            'message' => 'Tagihan pelanggan berhasil dihapus'
        ]);
    }
}

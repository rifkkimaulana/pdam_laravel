<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembayaranPelanggan;

class PembayaranController extends Controller
{
    // GET semua pembayaran pelanggan
    public function index()
    {
        return response()->json(PembayaranPelanggan::orderBy('tanggal_bayar', 'desc')->get());
    }

    // GET detail pembayaran berdasarkan ID
    public function show($id)
    {
        return response()->json(PembayaranPelanggan::findOrFail($id));
    }

    // POST tambah pembayaran baru
    public function store(Request $request)
    {
        $request->validate([
            'tagihan_id' => 'required|exists:tb_tagihan_pelanggan,id',
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:cash,transfer',
            'bukti_transfer' => 'nullable|string',
            'dicatat_oleh' => 'required|exists:tb_user,id',
        ]);

        $pembayaran = PembayaranPelanggan::create($request->all());
        return response()->json([
            'message' => 'Pembayaran berhasil ditambahkan!',
            'pembayaran' => $pembayaran
        ], 201);
    }

    // PUT update pembayaran
    public function update(Request $request, $id)
    {
        $pembayaran = PembayaranPelanggan::findOrFail($id);

        $request->validate([
            'jumlah_bayar' => 'sometimes|required|numeric|min:0',
            'metode_pembayaran' => 'sometimes|required|in:cash,transfer',
            'bukti_transfer' => 'nullable|string',
        ]);

        $pembayaran->update($request->all());
        return response()->json([
            'message' => 'Pembayaran berhasil diperbarui!',
            'pembayaran' => $pembayaran
        ]);
    }

    // DELETE pembayaran
    public function destroy($id)
    {
        PembayaranPelanggan::destroy($id);
        return response()->json(['message' => 'Pembayaran berhasil dihapus']);
    }
}

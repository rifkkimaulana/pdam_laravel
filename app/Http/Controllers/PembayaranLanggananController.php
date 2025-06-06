<?php

namespace App\Http\Controllers;

use App\Models\PembayaranLangganan;
use App\Models\Langganan;
use App\Models\Pengelola;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranLanggananController extends Controller
{
    // Tampilkan semua pembayaran
    public function index()
    {
        $pembayaran_langganan = PembayaranLangganan::with('langganan')->latest()->get();

        return response()->json($pembayaran_langganan);
    }

    // Tampilkan pembayaran berdasarkan ID
    public function show($id)
    {
        $pembayaran =   PembayaranLangganan::with('langganan')->findOrFail($id);
        return response()->json($pembayaran);
    }

    // Tambah pembayaran baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'langganan_id' => 'required|exists:tb_langganan,id',
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric',
            'metode' => 'required|in:Cash,Transfer',
            'status' => 'required|in:Menunggu,Diterima,Ditolak',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $pembayaran = new PembayaranLangganan();
        $pembayaran->langganan_id = $validated['langganan_id'];
        $pembayaran->tanggal_bayar = $validated['tanggal_bayar'];
        $pembayaran->jumlah_bayar = $validated['jumlah_bayar'];
        $pembayaran->metode = $validated['metode'];
        $pembayaran->status = $validated['status'];

        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $path = $file->store('bukti_bayar', 'public');
            $pembayaran->bukti_bayar = $path;
        }

        $pembayaran->save();

        return response()->json(['message' => 'Data pembayaran berhasil disimpan', 'data' => $pembayaran], 201);
    }


    // Update pembayaran
    public function update(Request $request, $id)
    {
        $pembayaran = PembayaranLangganan::findOrFail($id);

        $request->validate([
            'langganan_id' => 'required|exists:tb_langganan,id',
            'tanggal_bayar' => 'required|date',
            'metode' => 'required|in:Cash,Transfer',
            'status' => 'required|in:Menunggu,Diterima,Ditolak',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $langganan = Langganan::findOrFail($request->langganan_id);
        $jumlah_bayar = $langganan->paket->harga;

        $data = $request->only(['langganan_id', 'tanggal_bayar', 'metode', 'status']);
        $data['jumlah_bayar'] = $jumlah_bayar;

        if ($request->hasFile('bukti_bayar')) {
            // Hapus file lama jika ada
            if ($pembayaran->bukti_bayar) {
                Storage::delete($pembayaran->bukti_bayar);
            }
            $data['bukti_bayar'] = $request->file('bukti_bayar')->store('bukti_bayar');
        }

        $pembayaran->update($data);

        return response()->json($pembayaran);
    }

    // Hapus pembayaran
    public function destroy($id)
    {
        $pembayaran = PembayaranLangganan::findOrFail($id);

        // Hapus file bukti bayar jika ada
        if ($pembayaran->bukti_bayar) {
            Storage::delete($pembayaran->bukti_bayar);
        }

        $pembayaran->delete();

        return response()->json(['message' => 'Data pembayaran berhasil dihapus']);
    }
}

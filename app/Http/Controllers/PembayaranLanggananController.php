<?php

namespace App\Http\Controllers;

use App\Models\PembayaranLangganan;
use App\Models\Langganan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranLanggananController extends Controller
{
    // Tampilkan semua pembayaran
    public function index()
    {
        $pembayaran = PembayaranLangganan::with('langganan')->get();
        return response()->json($pembayaran);
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
        $request->validate([
            'langganan_id' => 'required|exists:tb_langganan,id',
            'tanggal_bayar' => 'required|date',
            'metode' => 'required|in:Cash,Transfer',
            'status' => 'required|in:Menunggu,Diterima,Ditolak',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Ambil jumlah bayar otomatis berdasarkan paket berlangganan
        $langganan = Langganan::findOrFail($request->langganan_id);
        $jumlah_bayar = $langganan->paket->harga;  // asumsikan relasi paket ada dan punya kolom harga

        $data = $request->only(['langganan_id', 'tanggal_bayar', 'metode', 'status']);
        $data['jumlah_bayar'] = $jumlah_bayar;

        // Upload bukti bayar jika ada
        if ($request->hasFile('bukti_bayar')) {
            $data['bukti_bayar'] = $request->file('bukti_bayar')->store('bukti_bayar');
        }

        $pembayaran = PembayaranLangganan::create($data);

        return response()->json($pembayaran, 201);
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

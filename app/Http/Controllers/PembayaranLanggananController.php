<?php

namespace App\Http\Controllers;

use App\Models\PembayaranLangganan;
use App\Models\Langganan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranLanggananController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search', '');
        $filter = $request->get('filter', '');

        $pembayaran = PembayaranLangganan::select('id', 'langganan_id', 'tanggal_bayar', 'jumlah_bayar', 'metode', 'status', 'bukti_bayar')
            ->with('langganan:id,pengelola_id,paket_id,status')
            ->with('langganan.pengelola:id,user_id,nama_pengelola')
            ->with('langganan.paket:id,nama_paket,harga_paket')
            ->with('langganan.user:id,nama_lengkap');

        // Filter Status Pembayaran
        if ($filter) {
            $pembayaran = $pembayaran->where('status', $filter);
        }

        // Pencarian nama lengkap pengguna atau nama pengelola
        if ($search) {
            $pembayaran = $pembayaran->whereHas('langganan.user', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%$search%");
            })->orWhereHas('langganan.pengelola', function ($q) use ($search) {
                $q->where('nama_pengelola', 'like', "%$search%");
            });
        }

        $pembayaran = $pembayaran->paginate($limit);

        return response()->json($pembayaran);
    }

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

        $data = $validated;

        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $filename = uniqid('bukti_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('private-file/bukti_bayar', $filename, 'local');
            $data['bukti_bayar'] = 'bukti_bayar/' . $filename;
        }

        $pembayaran = PembayaranLangganan::create($data);

        return response()->json(['message' => 'Data pembayaran berhasil disimpan', 'data' => $pembayaran], 201);
    }

    // // Tampilkan pembayaran berdasarkan ID
    // public function show($id)
    // {
    //     $pembayaran =   PembayaranLangganan::with('langganan')->findOrFail($id);
    //     return response()->json($pembayaran);
    // }

    // // Update pembayaran
    // public function update(Request $request, $id)
    // {
    //     $pembayaran = PembayaranLangganan::findOrFail($id);

    //     $request->validate([
    //         'langganan_id' => 'required|exists:tb_langganan,id',
    //         'tanggal_bayar' => 'required|date',
    //         'metode' => 'required|in:Cash,Transfer',
    //         'status' => 'required|in:Menunggu,Diterima,Ditolak',
    //         'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    //     ]);

    //     $langganan = Langganan::findOrFail($request->langganan_id);
    //     $jumlah_bayar = $langganan->paket->harga;

    //     $data = $request->only(['langganan_id', 'tanggal_bayar', 'metode', 'status']);
    //     $data['jumlah_bayar'] = $jumlah_bayar;

    //     if ($request->hasFile('bukti_bayar')) {
    //         // Hapus file lama jika ada
    //         if ($pembayaran->bukti_bayar) {
    //             Storage::delete($pembayaran->bukti_bayar);
    //         }
    //         $data['bukti_bayar'] = $request->file('bukti_bayar')->store('bukti_bayar');
    //     }

    //     $pembayaran->update($data);

    //     return response()->json($pembayaran);
    // }

    // // Hapus pembayaran
    // public function destroy($id)
    // {
    //     $pembayaran = PembayaranLangganan::findOrFail($id);

    //     // Hapus file bukti bayar jika ada
    //     if ($pembayaran->bukti_bayar) {
    //         Storage::delete($pembayaran->bukti_bayar);
    //     }

    //     $pembayaran->delete();

    //     return response()->json(['message' => 'Data pembayaran berhasil dihapus']);
    // }
}

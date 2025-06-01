<?php

// app/Http/Controllers/PembayaranController.php
namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'tagihan_id' => 'required|exists:tb_tagihan_pelanggan,id',
            'jumlah_bayar' => 'required|numeric',
            'tanggal_bayar' => 'required|date',
            'metode_pembayaran' => 'required|in:cash,transfer',
            'bukti_transfer' => 'nullable|string',
            'dicatat_oleh' => 'required|exists:tb_user,id',
            'status' => 'required|in:Pending,Terverifikasi,Ditolak',
        ]);

        $pembayaran = Pembayaran::create($data);

        if ($data['status'] === 'Terverifikasi') {
            Tagihan::find($pembayaran->tagihan_id)->update(['status' => 'Lunas']);
        }

        return response()->json(['message' => 'Pembayaran berhasil dicatat', 'data' => $pembayaran]);
    }
}

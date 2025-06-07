<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\TagihanPelanggan;
use App\Models\Gangguan;
use App\Models\PembayaranPelanggan;

class DashboardController extends Controller
{
    // // Data Ringkasan Dashboard
    // public function index()
    // {
    //     return response()->json([
    //         'total_pelanggan' => Pelanggan::count(),
    //         'total_tagihan' => TagihanPelanggan::count(),
    //         'total_gangguan' => Gangguan::count(),
    //         'total_pembayaran' => PembayaranPelanggan::count(),
    //     ]);
    // }

    // // Statistik Penggunaan Air
    // public function statistik()
    // {
    //     $statistik = TagihanPelanggan::selectRaw('periode, SUM(jumlah_tagihan) as total_pemakaian')
    //         ->groupBy('periode')
    //         ->get();

    //     return response()->json($statistik);
    // }

    // // Laporan Tagihan Pelanggan
    // public function laporanTagihan()
    // {
    //     return response()->json(TagihanPelanggan::orderBy('periode', 'desc')->get());
    // }

    // // Laporan Gangguan
    // public function laporanGangguan()
    // {
    //     return response()->json(Gangguan::orderBy('tanggal_lapor', 'desc')->get());
    // }
}

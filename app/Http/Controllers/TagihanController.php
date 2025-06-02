<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use Illuminate\Http\Request;

use App\Models\Pelanggan;
use App\Models\Meteran;
use Carbon\Carbon;

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

    // Generate tagihan massal berdasarkan periode (YYYY-MM) dan optional daftar pelanggan
    public function generateTagihanMassal(Request $request)
    {
        $request->validate([
            'periode'       => 'required|date_format:Y-m',
            'pelanggan_ids' => 'sometimes|array',
            'pelanggan_ids.*' => 'integer|exists:tb_pelanggan,id',
        ]);

        $periode       = $request->periode;       // contoh "2025-06"
        $tanggalAwal   = $periode . '-01';
        $tanggalAkhir  = Carbon::parse($tanggalAwal)->endOfMonth()->format('Y-m-d');

        // Query pelanggan yang status ="enable"
        $query = Pelanggan::where('status', 'enable');
        if ($request->filled('pelanggan_ids')) {
            $query->whereIn('id', $request->pelanggan_ids);
        }
        $daftarPelanggan = $query->get();

        foreach ($daftarPelanggan as $pel) {
            // Meteran sebelum periode (tanggal < 1 Juni) cari angka_meter paling akhir
            $meterAwal = Meteran::where('pelanggan_id', $pel->id)
                ->where('tanggal_catat', '<', $tanggalAwal)
                ->orderBy('tanggal_catat', 'desc')
                ->value('angka_meter') ?? 0;

            // Meteran di periode (1 Juni – 30 Juni) cari angka_meter paling akhir
            $meterAkhir = Meteran::where('pelanggan_id', $pel->id)
                ->whereBetween('tanggal_catat', [$tanggalAwal, $tanggalAkhir])
                ->orderBy('tanggal_catat', 'desc')
                ->value('angka_meter') ?? $meterAwal;

            // Cek jika tagihan untuk periode ini sudah ada
            $cek = Tagihan::where('pelanggan_id', $pel->id)
                ->where('periode', $tanggalAwal)
                ->first();
            if ($cek) continue;

            // Buat tagihan baru (potongan = 0)
            Tagihan::buatTagihanBaru($pel, $meterAwal, $meterAkhir, $tanggalAwal, 0);
        }

        return response()->json([
            'message' => 'Tagihan massal periode ' . $periode . ' berhasil dibuat.'
        ], 201);
    }

    // Cetak tagihan massal: return data tagihan untuk periode + daftar pelanggan
    public function cetakTagihanMassal(Request $request)
    {
        $request->validate([
            'periode'       => 'required|date_format:Y-m',
            'pelanggan_ids' => 'required|array',
            'pelanggan_ids.*' => 'integer|exists:tb_pelanggan,id',
        ]);

        $periode     = $request->periode . '-01';
        $pelangganIds = $request->pelanggan_ids;

        $tagihanList = Tagihan::with('pelanggan')
            ->whereIn('pelanggan_id', $pelangganIds)
            ->where('periode', $periode)
            ->get();

        if ($tagihanList->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada tagihan untuk periode ' . $request->periode
                    . ' dan pelanggan yang dipilih.'
            ], 404);
        }

        $result = $tagihanList->map(function ($t) {
            return [
                'id'                => $t->id,
                'pelanggan_id'      => $t->pelanggan_id,
                'nama_pelanggan'    => $t->pelanggan->user->nama_lengkap ?? null,
                'periode'           => $t->periode,
                'jumlah_tagihan'    => $t->jumlah_tagihan,
                'denda'             => $t->denda,
                'potongan'          => $t->jumlah_potongan,
                'tanggal_jatuh_tempo' => $t->tanggal_jatuh_tempo,
                'status'            => $t->status,
            ];
        });

        return response()->json(['data' => $result], 200);
    }

    public function cetakUlangTagihan(Request $request)
    {
        $request->validate([
            'periode'        => 'required|date_format:Y-m',
            'pelanggan_ids'  => 'required|array',
            'pelanggan_ids.*' => 'integer|exists:tb_pelanggan,id',
        ]);

        $periodeTanggal = $request->periode . '-01';
        $pelangganIds   = $request->pelanggan_ids;

        // Ambil semua tagihan yang sudah ada untuk periode + pelanggan
        $tagihans = Tagihan::whereIn('pelanggan_id', $pelangganIds)
            ->where('periode', $periodeTanggal)
            ->get();

        foreach ($tagihans as $t) {
            // Ambil meter awal & meter akhir seperti di generate
            $pel = $t->pelanggan;
            $meterAwal = Meteran::where('pelanggan_id', $pel->id)
                ->where('tanggal_catat', '<', $periodeTanggal)
                ->orderBy('tanggal_catat', 'desc')
                ->value('angka_meter') ?? 0;

            $tglAkhirBln = Carbon::parse($periodeTanggal)->endOfMonth()->format('Y-m-d');
            $meterAkhir = Meteran::where('pelanggan_id', $pel->id)
                ->whereBetween('tanggal_catat', [$periodeTanggal, $tglAkhirBln])
                ->orderBy('tanggal_catat', 'desc')
                ->value('angka_meter') ?? $meterAwal;

            // Hitung ulang dengan tarif blok terbaru
            $paket = $pel->paketPengguna;
            $baru  = Tagihan::hitungTagihanBlok($meterAwal, $meterAkhir, $paket)
                - $t->jumlah_potongan; // jika potongan tetap

            // Update jumlah_tagihan saja (atau denda jika ada logika lain)
            $t->jumlah_tagihan = $baru;
            $t->save();
        }

        return response()->json([
            'message' => 'Tagihan periode ' . $request->periode . ' berhasil di‐update.'
        ]);
    }
}

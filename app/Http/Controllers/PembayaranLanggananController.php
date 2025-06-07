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

        // Ambil user id dan jabatan dari token
        $user = $request->user();
        $user_id = $user ? $user->id : null;
        $jabatan = $user ? ($user->jabatan ?? null) : null;

        // Cari pengelola_id berdasarkan user_id
        $pengelola_id = null;
        if ($user_id && $jabatan !== 'Administrator') {
            $pengelola = \App\Models\Pengelola::where('user_id', $user_id)->first();
            if ($pengelola) {
                $pengelola_id = $pengelola->id;
            }
        }

        $pembayaran = PembayaranLangganan::select('id', 'langganan_id', 'tanggal_bayar', 'jumlah_bayar', 'metode', 'status', 'bukti_bayar')
            ->with('langganan:id,pengelola_id,paket_id,status')
            ->with('langganan.pengelola:id,user_id,nama_pengelola')
            ->with('langganan.paket:id,nama_paket,harga_paket')
            ->with('langganan.pengelola.user:id,nama_lengkap');

        // Filter berdasarkan pengelola_id dari user token jika ditemukan dan bukan Administrator
        if ($pengelola_id) {
            $pembayaran = $pembayaran->whereHas('langganan', function ($q) use ($pengelola_id) {
                $q->where('pengelola_id', $pengelola_id);
            });
        }

        // Filter Status Pembayaran
        if ($filter) {
            $pembayaran = $pembayaran->where('status', $filter);
        }

        // Pencarian nama lengkap pengguna atau nama pengelola
        if ($search) {
            $pembayaran = $pembayaran->where(function ($query) use ($search) {
                $query->whereHas('langganan.user', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%$search%");
                })->orWhereHas('langganan.pengelola', function ($q) use ($search) {
                    $q->where('nama_pengelola', 'like', "%$search%");
                });
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

    public function destroy($id)
    {
        try {
            $pembayaran = PembayaranLangganan::findOrFail($id);

            // Hapus file bukti bayar jika ada
            if ($pembayaran->bukti_bayar) {
                $filePath = storage_path('app/private-file/' . $pembayaran->bukti_bayar);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $pembayaran->delete();

            return response()->json(['message' => 'Data pembayaran berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function update(Request $request, $id)
    {
        // Cari pembayaran berdasarkan id
        $pembayaran = PembayaranLangganan::find($id);
        if (!$pembayaran) {
            return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
        }

        // Validasi status
        $request->validate([
            'status' => 'required|in:Menunggu,Diterima,Ditolak',
        ]);

        // Jika status Diterima, update langganan
        if ($request->status === 'Diterima') {
            $langganan = Langganan::with('paket')->find($pembayaran->langganan_id);
            if (!$langganan) {
                return response()->json(['message' => 'Langganan tidak ditemukan'], 404);
            }
            $paket = $langganan->paket;
            if (!$paket) {
                return response()->json(['message' => 'Paket tidak ditemukan'], 404);
            }

            // Hitung mulai dan akhir langganan (perpanjangan)
            $now = now();
            $akhirLama = $langganan->akhir_langganan ? \Carbon\Carbon::parse($langganan->akhir_langganan) : null;
            if ($akhirLama && $now->lt($akhirLama)) {
                $mulai = $akhirLama->copy();
            } else {
                $mulai = $now;
            }
            $satuan = strtolower($paket->satuan ?? 'hari');
            $masaAktif = (int)($paket->masa_aktif ?? 30);
            switch ($satuan) {
                case 'hari':
                    $akhir = $mulai->copy()->addDays($masaAktif);
                    break;
                case 'bulan':
                    $akhir = $mulai->copy()->addMonths($masaAktif);
                    break;
                case 'tahun':
                    $akhir = $mulai->copy()->addYears($masaAktif);
                    break;
                default:
                    $akhir = $mulai->copy()->addDays($masaAktif);
                    break;
            }
            $langganan->mulai_langganan = $mulai;
            $langganan->akhir_langganan = $akhir;
            $langganan->status = 'Aktif';
            $langganan->save();
        }

        // Update status pembayaran
        $pembayaran->status = $request->status;
        $pembayaran->save();

        return response()->json(['message' => 'Status pembayaran berhasil diupdate', 'data' => $pembayaran]);
    }

    // // Tampilkan pembayaran berdasarkan ID
    // public function show($id)
    // {
    //     $pembayaran =   PembayaranLangganan::with('langganan')->findOrFail($id);
    //     return response()->json($pembayaran);
    // }
}

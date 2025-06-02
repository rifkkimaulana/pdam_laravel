<?php

namespace App\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tb_tagihan_pelanggan';

    protected $fillable = [
        'pelanggan_id',
        'periode',
        'jumlah_tagihan',
        'denda',
        'tanggal_jatuh_tempo',
        'status',
        'keterangan_potongan',
        'jumlah_potongan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }


    /**
     * Hitung biaya berdasarkan blok tarif.
     *
     * @param int $angkaAwal   Nilai meter periode sebelumnya
     * @param int $angkaAkhir  Nilai meter periode sekarang
     * @param PaketPengguna $paketInstance
     * @return float
     */
    public static function hitungTagihanBlok(int $angkaAwal, int $angkaAkhir, PaketPengguna $paketInstance): float
    {
        $pemakaian  = max(0, $angkaAkhir - $angkaAwal);
        $sisa       = $pemakaian;
        $total      = 0;
        $batasBawah = 0;

        // Ambil daftar blok tarif untuk paket ini, urut berdasarkan batas_atas
        $blokList = $paketInstance
            ->blokTarif()
            ->orderBy('batas_atas')
            ->get();

        foreach ($blokList as $blok) {
            if ($sisa <= 0) break;

            $batasAtas = $blok->batas_atas;
            $volume    = min($sisa, $batasAtas - $batasBawah);
            $total   += $volume * floatval($blok->harga_per_m3);
            $sisa    -= $volume;
            $batasBawah = $batasAtas;
        }

        return $total;
    }

    /**
     * Buat satu record tagihan baru.
     *
     * @param Pelanggan $pel  Instance pelanggan
     * @param int $angkaAwal
     * @param int $angkaAkhir
     * @param string $periode  Format 'YYYY-MM-DD' (tanggal pertama bulan)
     * @param float $potonganRp
     * @return Tagihan
     */

    public static function buatTagihanBaru(
        Pelanggan $pel,
        int $angkaAwal,
        int $angkaAkhir,
        string $periode,
        float $potonganRp = 0
    ): Tagihan {
        // Pastikan relasi paketPengguna() di model Pelanggan sudah ada
        $paketInstance = $pel->paketPengguna;

        $dasarBiaya  = self::hitungTagihanBlok($angkaAwal, $angkaAkhir, $paketInstance);
        $denda       = 0;
        $jumlahPot   = $potonganRp;
        $total       = $dasarBiaya + $denda - $jumlahPot;

        return self::create([
            'pelanggan_id'        => $pel->id,
            'periode'             => Carbon::parse($periode)->format('Y-m-d'),
            'jumlah_tagihan'      => $total,
            'denda'               => $denda,
            'tanggal_jatuh_tempo' => Carbon::parse($periode)
                ->addMonth()
                ->endOfMonth()
                ->format('Y-m-d'),
            'status'              => 'Belum Bayar',
            'keterangan_potongan' => $jumlahPot > 0 ? 'Potongan promo' : '',
            'jumlah_potongan'     => $jumlahPot,
        ]);
    }
}

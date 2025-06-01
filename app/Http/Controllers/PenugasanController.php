<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenugasanGangguan;

class PenugasanController extends Controller
{
    /**
     * Menampilkan daftar semua penugasan dengan urutan berdasarkan tanggal tugas terbaru.
     */
    public function index()
    {
        $penugasans = PenugasanGangguan::orderBy('tanggal_tugas', 'desc')->get();
        return response()->json($penugasans);
    }

    /**
     * Menampilkan detail penugasan berdasarkan ID.
     */
    public function show($id)
    {
        $penugasan = PenugasanGangguan::findOrFail($id);
        return response()->json($penugasan);
    }

    /**
     * Menambahkan penugasan baru.
     * Validasi yang dilakukan:
     * - gangguan_id harus ada dan merupakan ID yang valid di tabel gangguan.
     * - staf_id harus ada dan merupakan ID yang valid di tabel staf.
     * - tanggal_tugas opsional, jika diberikan harus berupa format tanggal.
     * - status_kerja dapat bernilai: ditugaskan, sedang_kerja, selesai, atau batal.
     * - catatan opsional.
     */
    public function store(Request $request)
    {
        $request->validate([
            'gangguan_id'    => 'required|exists:tb_gangguan,id',
            'staf_id'        => 'required|exists:tb_staf,id',
            'tanggal_tugas'  => 'nullable|date',
            'status_kerja'   => 'nullable|in:ditugaskan,sedang_kerja,selesai,batal',
            'catatan'        => 'nullable|string'
        ]);

        $penugasan = PenugasanGangguan::create($request->all());

        return response()->json([
            'message'   => 'Penugasan berhasil dibuat!',
            'penugasan' => $penugasan
        ], 201);
    }

    /**
     * Mengupdate data penugasan.
     * Perbarui kolom yang diizinkan, misalnya:
     * - tanggal_tugas (jika ingin mengubah jadwal tugas)
     * - status_kerja (ubah status pengerjaan)
     * - catatan (update keterangan)
     */
    public function update(Request $request, $id)
    {
        $penugasan = PenugasanGangguan::findOrFail($id);

        $request->validate([
            'tanggal_tugas'  => 'sometimes|required|date',
            'status_kerja'   => 'sometimes|required|in:ditugaskan,sedang_kerja,selesai,batal',
            'catatan'        => 'nullable|string'
        ]);

        $penugasan->update($request->all());

        return response()->json([
            'message'   => 'Penugasan berhasil diperbarui!',
            'penugasan' => $penugasan
        ]);
    }

    /**
     * Menghapus data penugasan berdasarkan ID.
     */
    public function destroy($id)
    {
        PenugasanGangguan::destroy($id);
        return response()->json(['message' => 'Penugasan berhasil dihapus!']);
    }
}

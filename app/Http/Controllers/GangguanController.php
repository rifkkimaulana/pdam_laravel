<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gangguan;

class GangguanController extends Controller
{
    // GET semua laporan gangguan
    public function index()
    {
        return response()->json(Gangguan::orderBy('tanggal_lapor', 'desc')->get());
    }

    // GET laporan gangguan berdasarkan ID
    public function show($id)
    {
        return response()->json(Gangguan::findOrFail($id));
    }

    // POST tambah laporan gangguan baru
    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggan,id',
            'pengelola_id' => 'required|exists:pengelola,id',
            'judul' => 'required|max:150',
            'deskripsi' => 'nullable',
            'foto_bukti' => 'nullable|string'
        ]);

        $gangguan = Gangguan::create($request->all());
        return response()->json([
            'message' => 'Laporan gangguan berhasil ditambahkan!',
            'gangguan' => $gangguan
        ], 201);
    }

    // PUT update status gangguan
    public function update(Request $request, $id)
    {
        $gangguan = Gangguan::findOrFail($id);

        $request->validate([
            'status' => 'required|in:baru,diproses,selesai,batal',
        ]);

        $gangguan->update($request->all());
        return response()->json(['message' => 'Status gangguan diperbarui!', 'gangguan' => $gangguan]);
    }

    // DELETE laporan gangguan
    public function destroy($id)
    {
        Gangguan::destroy($id);
        return response()->json(['message' => 'Laporan gangguan berhasil dihapus']);
    }
}

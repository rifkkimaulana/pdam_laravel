<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gangguan;

class GangguanController extends Controller
{
    public function index()
    {
        return response()->json(Gangguan::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id' => 'required|exists:tb_pelanggan,id',
            'pengelola_id' => 'required|exists:tb_pengelola,id',
            'judul' => 'required|string',
            'deskripsi' => 'required|string',
        ]);
        $data['status'] = 'Baru';
        return response()->json(['message' => 'Komplain ditambahkan', 'data' => Gangguan::create($data)]);
    }

    public function update(Request $request, $id)
    {
        $gangguan = Gangguan::findOrFail($id);
        $gangguan->update($request->only(['judul', 'deskripsi', 'status']));
        return response()->json(['message' => 'Komplain diperbarui', 'data' => $gangguan]);
    }

    public function destroy($id)
    {
        Gangguan::destroy($id);
        return response()->json(['message' => 'Komplain dihapus']);
    }
}

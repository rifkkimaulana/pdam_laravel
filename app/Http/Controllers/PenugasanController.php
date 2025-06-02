<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use App\Models\Gangguan;
use Illuminate\Http\Request;

class PenugasanController extends Controller
{
    public function index()
    {
        return Penugasan::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'gangguan_id' => 'required|exists:tb_gangguan,id',
            'staf_id' => 'required|exists:tb_staf,id',
            'status_kerja' => 'required|in:Ditugaskan,Sedang Dikerjakan,Selesai,Batal',
            'catatan' => 'nullable|string',
        ]);

        $penugasan = Penugasan::create($data);

        return response()->json($penugasan, 201);
    }

    public function show($id)
    {
        $penugasan = Penugasan::findOrFail($id);
        return response()->json($penugasan);
    }

    public function update(Request $request, $id)
    {
        $penugasan = Penugasan::findOrFail($id);

        $data = $request->validate([
            'status_kerja' => 'required|in:Ditugaskan,Sedang Dikerjakan,Selesai,Batal',
            'catatan' => 'nullable|string',
        ]);

        $penugasan->update($data);
        $gangguan = Gangguan::find($penugasan->gangguan_id);

        if ($gangguan) {
            if ($data['status_kerja'] === 'Ditugaskan') {
                $gangguan->status = 'Baru';
            } else if ($data['status_kerja'] === 'Sedang Dikerjakan') {
                $gangguan->status = 'Diproses';
            } else if ($data['status_kerja'] === 'Selesai') {
                $gangguan->status = 'Selesai';
            } else if ($data['status_kerja'] === 'Batal') {
                $gangguan->status = 'Batal';
            }

            $gangguan->save();
        }
        return response()->json($penugasan);
    }

    public function destroy($id)
    {
        $penugasan = Penugasan::findOrFail($id);
        $penugasan->delete();

        return response()->json(['message' => 'Penugasan dihapus']);
    }
}

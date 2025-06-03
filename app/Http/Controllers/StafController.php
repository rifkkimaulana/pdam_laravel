<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staf;

class StafController extends Controller
{

    public function index()
    {
        $staf = Staf::with(['pengelola', 'user'])->get();

        $staf->transform(function ($item) {
            $item->user_pengelola = $item->pengelola ? $item->pengelola->user : null;
            return $item;
        });

        return response()->json($staf);
    }


    public function show($id)
    {
        return response()->json(Staf::with('pengelola', 'user')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:tb_user,id',
            'pengelola_id' => 'required|exists:tb_pengelola,id',
            'jabatan' => 'required|string|max:255',
        ]);

        $staf = Staf::create($request->all());
        return response()->json([
            'message' => 'Staf berhasil ditambahkan!',
            'staf' => $staf
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $staf = Staf::findOrFail($id);

        $request->validate([
            'user_id' => 'sometimes|required|exists:tb_user,id',
            'pengelola_id' => 'sometimes|required|exists:tb_pengelola,id',
            'jabatan' => 'sometimes|required|string|max:255',
        ]);

        $staf->update($request->all());
        return response()->json([
            'message' => 'Staf berhasil diperbarui!',
            'staf' => $staf
        ]);
    }


    public function destroy($id)
    {
        Staf::destroy($id);
        return response()->json(['message' => 'Staf berhasil dihapus']);
    }
}

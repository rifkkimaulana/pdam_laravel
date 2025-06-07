<?php

namespace App\Http\Controllers;

use App\Models\Pengelola;
use App\Models\Langganan;
use Illuminate\Http\Request;

class PengelolaController extends Controller
{

    public function index(Request $request)
    {
        $pengelolas = Pengelola::select('id', 'user_id', 'nama_pengelola')
            ->with('user:id,nama_lengkap')->get();

        $pengelolas->transform(function ($pengelola) {
            $pengelola->langganan = Langganan::where('pengelola_id', $pengelola->id)->select('id', 'paket_id', 'status')
                ->with(['paket:id,nama_paket,harga_paket'])
                ->get();
            return $pengelola;
        });
        return response()->json($pengelolas);
    }

    // // GET /pengelola/{id}
    // public function show($id)
    // {
    //     $pengelola = Pengelola::find($id);
    //     if (!$pengelola) {
    //         return response()->json(['message' => 'Pengelola tidak ditemukan'], 404);
    //     }
    //     return response()->json($pengelola);
    // }

    // POST /pengelola
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'user_id' => 'nullable|integer',
    //         'nama_pengelola' => 'required|string|max:255',
    //         'email' => 'nullable|email',
    //         'telpon' => 'nullable|string|max:50',
    //         'alamat' => 'nullable|string',
    //         'logo' => 'nullable|string',
    //         'deskripsi' => 'nullable|string',
    //     ]);
    //     $pengelola = Pengelola::create($validated);
    //     return response()->json($pengelola, 201);
    // }

    // // PUT /pengelola/{id}
    // public function update(Request $request, $id)
    // {
    //     $pengelola = Pengelola::find($id);
    //     if (!$pengelola) {
    //         return response()->json(['message' => 'Pengelola tidak ditemukan'], 404);
    //     }
    //     $validated = $request->validate([
    //         'user_id' => 'nullable|integer',
    //         'nama_pengelola' => 'required|string|max:255',
    //         'email' => 'nullable|email',
    //         'telpon' => 'nullable|string|max:50',
    //         'alamat' => 'nullable|string',
    //         'logo' => 'nullable|string',
    //         'deskripsi' => 'nullable|string',
    //     ]);
    //     $pengelola->update($validated);
    //     return response()->json($pengelola);
    // }

    // // DELETE /pengelola/{id}
    // public function destroy($id)
    // {
    //     $pengelola = Pengelola::find($id);
    //     if (!$pengelola) {
    //         return response()->json(['message' => 'Pengelola tidak ditemukan'], 404);
    //     }
    //     $pengelola->delete();
    //     return response()->json(['message' => 'Pengelola berhasil dihapus']);
    // }


}

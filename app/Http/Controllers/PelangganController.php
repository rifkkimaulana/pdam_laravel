<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;

class PelangganController extends Controller
{
    // GET semua pelanggan
    public function index()
    {
        return response()->json(Pelanggan::orderBy('created_at', 'desc')->get());
    }

    // GET pelanggan berdasarkan ID
    public function show($id)
    {
        return response()->json(Pelanggan::findOrFail($id));
    }

    // POST tambah pelanggan baru
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:tb_user,id',
            'pengelola_id' => 'required|exists:tb_pengelola,id',
            'no_meter' => 'required',
            'alamat_meter' => 'required',
            'status' => 'required|in:enable,disable',
        ]);

        $pelanggan = Pelanggan::create($request->all());
        return response()->json([
            'message' => 'Pelanggan berhasil ditambahkan!',
            'pelanggan' => $pelanggan
        ], 201);
    }

    // PUT update pelanggan
    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $request->validate([
            'no_meter' => 'sometimes|required',
            'alamat_meter' => 'sometimes|required',
            'status' => 'sometimes|required|in:enable,disable',
        ]);

        $pelanggan->update($request->all());
        return response()->json([
            'message' => 'Pelanggan berhasil diperbarui!',
            'pelanggan' => $pelanggan
        ]);
    }

    // DELETE pelanggan
    public function destroy($id)
    {
        Pelanggan::destroy($id);
        return response()->json(['message' => 'Pelanggan berhasil dihapus']);
    }
}

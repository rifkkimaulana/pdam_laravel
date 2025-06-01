<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Tampilkan daftar semua role.
     */
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    /**
     * Tampilkan detail role berdasarkan ID.
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    /**
     * Simpan role baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_role' => 'required|string|max:20',
        ]);

        $role = Role::create([
            'nama_role' => $request->nama_role,
        ]);

        return response()->json([
            'message' => 'Role berhasil ditambahkan!',
            'role'    => $role,
        ], 201);
    }

    /**
     * Perbarui role yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_role' => 'required|string|max:20',
        ]);

        $role = Role::findOrFail($id);
        $role->update($request->all());

        return response()->json([
            'message' => 'Role berhasil diperbarui!',
            'role'    => $role,
        ]);
    }

    /**
     * Hapus role berdasarkan ID.
     */
    public function destroy($id)
    {
        Role::destroy($id);

        return response()->json([
            'message' => 'Role berhasil dihapus!'
        ]);
    }
}

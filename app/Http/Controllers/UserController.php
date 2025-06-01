<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pengelola;
use App\Models\Pelanggan;
use App\Models\Staf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::with(['pengelola', 'pelanggan', 'staf'])->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap'     => 'required|string|max:50',
            'username'         => 'required|string|max:50|unique:tb_user,username',
            'password'         => 'required|string|min:6',
            'email'            => 'required|email|max:50|unique:tb_user,email',
            'telpon'           => 'required|string|max:15',
            'jenis_identitas'  => 'required|in:KTP,SIM,PASPOR,ID Lainnya',
            'nomor_identitas'  => 'required|string|max:20',
            'file_identitas'   => 'required|string',
            'alamat'           => 'required|string',
            'pictures'         => 'required|string',
            'jabatan'          => 'required|in:Administrator,Pengelola,Pelanggan,Staf'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        // Simpan detail berdasarkan jabatan
        switch ($validated['jabatan']) {
            case 'Pengelola':
                Pengelola::create([
                    'user_id'        => $user->id,
                    'nama_pengelola' => $request->nama_pengelola,
                    'email'          => $request->email_pengelola,
                    'telpon'         => $request->telpon_pengelola,
                    'alamat'         => $request->alamat_pengelola,
                    'logo'           => $request->logo,
                    'deskripsi'      => $request->deskripsi
                ]);
                break;

            case 'Pelanggan':
                Pelanggan::create([
                    'user_id'      => $user->id,
                    'pengelola_id' => $request->pengelola_id,
                    'no_meter'     => $request->no_meter,
                    'alamat_meter' => $request->alamat_meter,
                    'status'       => 'enable'
                ]);
                break;

            case 'Staf':
                Staf::create([
                    'user_id'      => $user->id,
                    'pengelola_id' => $request->pengelola_id,
                    'jabatan'      => $request->jabatan_staf
                ]);
                break;
        }

        return response()->json(['message' => 'User berhasil ditambahkan', 'user' => $user], 201);
    }

    public function show($id)
    {
        $user = User::with(['pengelola', 'pelanggan', 'staf'])->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {

        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap'     => 'sometimes|required|string|max:50',
            'username'         => 'sometimes|required|string|max:50|unique:tb_user,username,' . $id,
            'email'            => 'sometimes|required|email|max:50|unique:tb_user,email,' . $id,
            'password'         => 'sometimes|nullable|string|min:6',
            'telpon'           => 'sometimes|required|string|max:15',
            'jenis_identitas'  => 'sometimes|required|in:KTP,SIM,PASPOR,ID Lainnya',
            'nomor_identitas'  => 'sometimes|required|string|max:20',
            'file_identitas'   => 'sometimes|required|string',
            'alamat'           => 'sometimes|required|string',
            'pictures'         => 'sometimes|required|string',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'User berhasil diperbarui', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->jabatan == 'Pengelola') {
            Pengelola::where('user_id', $id)->delete();
        } elseif ($user->jabatan == 'Pelanggan') {
            Pelanggan::where('user_id', $id)->delete();
        } elseif ($user->jabatan == 'Staf') {
            Staf::where('user_id', $id)->delete();
        }

        $user->delete();

        return response()->json(['message' => 'User dan data terkait berhasil dihapus!'], 200);
    }
}

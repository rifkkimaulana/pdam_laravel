<?php

namespace App\Http\Controllers;

use App\Models\Langganan;
use App\Models\PaketLangganan;
use App\Models\User;
use App\Models\Pengelola;
use App\Models\Pelanggan;
use App\Models\Staf;
use App\Models\PaketPengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    private function getUserData($user)
    {
        $row = [];
        switch ($user->jabatan) {
            case 'Administrator':
                $row['user'] = $user->toArray();
                $row['users'] = User::latest()->get()->toArray();
                break;

            case 'Pengelola':
                $row['user'] = $user->toArray();

                $userPengelola = $user->pengelola;

                $row['pengelola'] = $userPengelola ? $userPengelola->toArray() : [];

                $row['paket'] = $userPengelola && $userPengelola->paket_id ?
                    PaketLangganan::find($userPengelola->paket_id)->toArray() : [];

                $langganan = $userPengelola ?
                    Langganan::where('pengelola_id', $userPengelola->id)->first() : null;

                $row['langganan'] = $langganan ? $langganan->toArray() : null;
                break;


            case 'Pelanggan':
                $row['user'] = $user->toArray();
                $row['pelanggan'] = $user->pelanggan ? $user->pelanggan->toArray() : [];
                $pengelolaPelanggan = Pengelola::find($user->pelanggan->pengelola_id);
                $row['pengelola_pelanggan'] = $pengelolaPelanggan ? $pengelolaPelanggan->toArray() : [];
                $userPengelolaPelanggan = $pengelolaPelanggan ? User::find($pengelolaPelanggan->user_id) : null;
                $row['user_pengelola_pelanggan'] = $userPengelolaPelanggan ? $userPengelolaPelanggan->toArray() : [];
                $paket = PaketPengguna::find($user->pelanggan->paket_id);
                $row['paket'] = $paket ? $paket->toArray() : [];
                break;

            case 'Staf':
                $row['user'] = $user->toArray();
                $row['staf'] = $user->staf ? $user->staf->toArray() : [];
                $stafUser = $user->staf ? User::find($user->staf->user_id) : null;
                $row['staf_user'] = $stafUser ? $stafUser->toArray() : [];
                $stafPengelola = $user->staf && $user->staf->pengelola_id ? Pengelola::find($user->staf->pengelola_id) : null;
                $row['staf_pengelola'] = $stafPengelola ? $stafPengelola->toArray() : [];
                $userPengelola = $stafPengelola ? User::find($stafPengelola->user_id) : null;
                $row['user_pengelola'] = $userPengelola ? $userPengelola->toArray() : [];
                break;
        }
        return $row;
    }

    public function index()
    {
        $users = User::latest()->get();
        $result = [];

        foreach ($users as $user) {
            $row = $this->getUserData($user);
            if ($row) {
                $result[] = $row;
            }
        }

        return response()->json($result);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $row = $this->getUserData($user);

        return response()->json($row);
    }

    public function store(Request $request)
    {
        // Validasi input data
        $validated = $request->validate([
            'paket_id'         => 'sometimes|exists:tb_paket_langganan,id',
            'nama_lengkap'     => 'required|string|max:50',
            'username'         => 'required|string|max:50|unique:tb_user,username',
            'password'         => 'required|string|min:6',
            'email'            => 'required|email|max:50|unique:tb_user,email',
            'telpon'           => 'required|string|max:15',
            'jenis_identitas'  => 'required|in:KTP,SIM,PASPOR,ID Lainnya',
            'nomor_identitas'  => 'required|string|max:20',
            'file_identitas'   => 'nullable',
            'alamat'           => 'required|string',
            'pictures'         => 'nullable',
            'logo'             => 'nullable',
            'jabatan'          => 'required|in:Administrator,Pengelola,Pelanggan,Staf'
        ]);

        // Handle file upload for file_identitas
        if ($request->hasFile('file_identitas')) {
            $file = $request->file('file_identitas');
            $fileName = time() . '_' . uniqid() . '_identitas.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/identitas'), $fileName);
            $validated['file_identitas'] = 'storage/uploads/identitas/' . $fileName;
        } else if ($request->filled('file_identitas')) {
            $validated['file_identitas'] = $request->input('file_identitas');
        } else {
            $validated['file_identitas'] = '';
        }

        // Handle file upload for pictures
        if ($request->hasFile('pictures')) {
            $file = $request->file('pictures');
            $fileName = time() . '_' . uniqid() . '_foto.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pictures'), $fileName);
            $validated['pictures'] = 'storage/uploads/pictures/' . $fileName;
        } else if ($request->filled('pictures')) {
            $validated['pictures'] = $request->input('pictures');
        } else {
            $validated['pictures'] = '';
        }

        // Handle password hash
        $validated['password'] = Hash::make($validated['password']);

        // Create new user
        $user = User::create($validated);

        // Prepare response array to include all the added data
        $responseData = [
            'user' => $user,
            'pengelola' => null,
            'langganan' => null
        ];

        // Check if the user is a "Pengelola" and create the Pengelola
        if ($validated['jabatan'] === 'Pengelola') {
            // Create pengelola
            $pengelola = Pengelola::create([
                'user_id'        => $user->id,
                'paket_id'       => $request->paket_id,
                'nama_pengelola' => $request->nama_pengelola,
                'email'          => $request->email_pengelola,
                'telpon'         => $request->telpon_pengelola,
                'alamat'         => $request->alamat_pengelola,
                'logo'           => $request->logo_pengelola,
                'deskripsi'      => $request->deskripsi_pengelola
            ]);

            // Handle logo file upload for pengelola
            if ($request->hasFile('logo_pengelola')) {
                $file = $request->file('logo_pengelola');
                $fileName = time() . '_logo.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/logo'), $fileName);
                // Update logo path for pengelola
                $pengelola->update(['logo' => 'storage/uploads/logo/' . $fileName]);
            }

            // Create langganan entry for the pengelola (initial status "Tidak Aktif")
            $langganan = Langganan::create([
                'pengelola_id' => $pengelola->id,
                'status'       => 'Tidak Aktif',
                'paket_id'     => $request->paket_id,
                'user_id'      => 1, // Assuming user_id 1 is the admin or system user
                'mulai_langganan' => null,
                'akhir_langganan' => null
            ]);

            // Add the pengelola and langganan to the response
            $responseData['pengelola'] = $pengelola;
            $responseData['langganan'] = $langganan;
        }

        // Handle other jabatan (Pelanggan or Staf)
        switch ($validated['jabatan']) {
            case 'Pelanggan':
                $pelanggan = Pelanggan::create([
                    'user_id'      => $user->id,
                    'pengelola_id' => $request->pengelola_id,
                    'paket_id'     => $request->paket_id,
                    'no_meter'     => $request->no_meter,
                    'alamat_meter' => $request->alamat_meter,
                    'status'       => 'enable'
                ]);
                $responseData['pelanggan'] = $pelanggan;
                break;

            case 'Staf':
                $staf = Staf::create([
                    'user_id'      => $user->id,
                    'pengelola_id' => $request->pengelola_id,
                    'jabatan'      => $request->jabatan_staf
                ]);
                $responseData['staf'] = $staf;
                break;
        }

        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'data' => $responseData
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

        $validated = $request->validate([
            'nama_lengkap'     => 'sometimes|string|max:50',
            'username'         => 'sometimes|string|max:50|unique:tb_user,username,' . $id,
            'email'            => 'sometimes|email|max:50|unique:tb_user,email,' . $id,
            'password'         => 'sometimes|nullable|string|min:6',
            'telpon'           => 'sometimes|string|max:15',
            'jenis_identitas'  => 'sometimes|in:KTP,SIM,PASPOR,ID Lainnya',
            'nomor_identitas'  => 'sometimes|string|max:20',
            'file_identitas'   => 'nullable',
            'alamat'           => 'sometimes|string',
            'pictures'         => 'nullable',
        ]);

        $updateData = [];
        foreach ($validated as $key => $val) {
            if ($val !== null && $val !== "") {
                $updateData[$key] = $val;
            }
        }

        if (isset($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        if ($request->hasFile('file_identitas')) {
            if ($user->file_identitas && File::exists(public_path($user->file_identitas))) {
                File::delete(public_path($user->file_identitas));
            }
            $file = $request->file('file_identitas');
            $fileName = time() . '_identitas.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/identitas'), $fileName);
            $updateData['file_identitas'] = 'uploads/identitas/' . $fileName;
        } elseif ($request->filled('file_identitas')) {
            $updateData['file_identitas'] = $request->input('file_identitas');
        } elseif ($request->exists('file_identitas')) {
            if ($user->file_identitas && File::exists(public_path($user->file_identitas))) {
                File::delete(public_path($user->file_identitas));
            }
            $updateData['file_identitas'] = null;
        }

        if ($request->hasFile('pictures')) {
            if ($user->pictures && File::exists(public_path($user->pictures))) {
                File::delete(public_path($user->pictures));
            }
            $file = $request->file('pictures');
            $fileName = time() . '_foto.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pictures'), $fileName);
            $updateData['pictures'] = 'uploads/pictures/' . $fileName;
        } elseif ($request->filled('pictures')) {
            $updateData['pictures'] = $request->input('pictures');
        } elseif ($request->exists('pictures')) {
            if ($user->pictures && File::exists(public_path($user->pictures))) {
                File::delete(public_path($user->pictures));
            }
            $updateData['pictures'] = null;
        }

        $user->update($updateData);

        return response()->json(['message' => 'User berhasil diperbarui', 'user' => $user->fresh()]);
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

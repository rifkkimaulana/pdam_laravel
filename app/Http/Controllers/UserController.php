<?php

namespace App\Http\Controllers;

use App\Models\Langganan;
use App\Models\PaketLangganan;
use App\Models\User;
use App\Models\Pengelola;
use App\Models\Pelanggan;
use App\Models\Staf;
use App\Models\PaketPengguna;
use App\Models\Pembayaran;
use App\Models\PembayaranLangganan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $search = $request->input('search');
        $status = $request->input('status');

        if ($request->input('jabatan') === 'Pengelola') {
            $query = User::where('jabatan', $request->input('jabatan'))->latest();
        } else {
            $query = User::latest();
        }

        if ($search) {
            $query->where('nama_lengkap', 'like', '%' . $search . '%');
        }

        // Jika filter status aktif, ambil hanya Pengelola, lalu filter status di PHP
        if ($status) {
            $query->where('jabatan', 'Pengelola');
            $users = $query->get(); // Ambil semua dulu, filter manual
            $filtered = [];
            foreach ($users as $user) {
                $row = $this->getUserData($user);
                if (isset($row['langganan']['status']) && $row['langganan']['status'] === $status) {
                    $filtered[] = $row;
                }
            }
            // Paginate manual
            $total = count($filtered);
            $offset = ($page - 1) * $limit;
            $result = array_slice($filtered, $offset, $limit);

            return response()->json([
                'data' => $result,
                'total' => $total,
                'page' => (int)$page,
                'last_page' => ceil($total / $limit),
                'per_page' => (int)$limit,
            ]);
        } else {
            $users = $query->paginate($limit);
            $result = [];
            foreach ($users as $user) {
                $row = $this->getUserData($user);
                if ($row) {
                    $result[] = $row;
                }
            }
            return response()->json([
                'data' => $result,
                'total' => $users->total(),
                'page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
            ]);
        }
    }

    // public function show($id)
    // {
    //     $user = User::findOrFail($id);
    //     $row = $this->getUserData($user);

    //     return response()->json($row);
    // }

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
            $path = 'private-file/identitas/' . $fileName;
            Storage::disk('local')->put($path, file_get_contents($file));
            $validated['file_identitas'] = $path;
        } else if ($request->filled('file_identitas')) {
            $validated['file_identitas'] = $request->input('file_identitas');
        } else {
            $validated['file_identitas'] = '';
        }

        // Handle file upload for pictures
        if ($request->hasFile('pictures')) {
            $file = $request->file('pictures');
            $fileName = time() . '_' . uniqid() . '_foto.' . $file->getClientOriginalExtension();
            $path = 'private-file/pictures/' . $fileName;
            Storage::disk('local')->put($path, file_get_contents($file));
            $validated['pictures'] = $path;
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
                $path = 'private-file/logo/' . $fileName;
                Storage::disk('local')->put($path, file_get_contents($file));
                // Update logo path for pengelola
                $pengelola->update(['logo' => $path]);
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
        // Cari pengguna berdasarkan ID
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Validasi input data
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
            'jabatan'          => 'sometimes|in:Administrator,Pengelola,Pelanggan,Staf',
            'logo'             => 'nullable',
            'paket_id'         => 'sometimes|exists:tb_paket_langganan,id',
            'nama_pengelola'   => 'sometimes|string|max:100',
            'email_pengelola'  => 'sometimes|email|max:50',
            'telpon_pengelola' => 'sometimes|string|max:15',
            'alamat_pengelola' => 'sometimes|string',
            'deskripsi_pengelola' => 'sometimes|string',
        ]);

        // Perbarui data pengguna jika ada perubahan
        $updateData = [];
        foreach ($validated as $key => $val) {
            if ($val !== null && $val !== "") {
                $updateData[$key] = $val;
            }
        }

        // Cek jika ada permintaan perubahan password dan kolom password_lama
        if ($request->filled('password') && $request->filled('password_lama')) {
            // Verifikasi password lama
            if (!Hash::check($request->input('password_lama'), $user->password)) {
                return response()->json(['message' => 'Password lama salah'], 400);
            }
        }
        // Jika password_lama tidak ada, lanjutkan proses update seperti biasa

        // Jika ada perubahan password, lakukan hash
        if (isset($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        // Tangani upload file identitas
        if ($request->hasFile('file_identitas')) {
            // Hapus file lama jika ada
            if ($user->file_identitas && Storage::disk('local')->exists($user->file_identitas)) {
                Storage::disk('local')->delete($user->file_identitas);
            }

            $file = $request->file('file_identitas');
            $fileName = time() . '_identitas.' . $file->getClientOriginalExtension();
            $path = 'private-file/identitas/' . $fileName;
            Storage::disk('local')->put($path, file_get_contents($file));
            $updateData['file_identitas'] = $path;
        }

        // Tangani upload foto pengguna
        if ($request->hasFile('pictures')) {
            // Hapus file lama jika ada
            if ($user->pictures && Storage::disk('local')->exists($user->pictures)) {
                Storage::disk('local')->delete($user->pictures);
            }

            $file = $request->file('pictures');
            $fileName = time() . '_foto.' . $file->getClientOriginalExtension();
            $path = 'private-file/pictures/' . $fileName;
            Storage::disk('local')->put($path, file_get_contents($file));
            $updateData['pictures'] = $path;
        }

        // Perbarui data pengguna
        $user->update($updateData);

        // Perbarui data pengelola jika jabatan adalah "Pengelola"
        if ($user->jabatan === 'Pengelola') {
            $pengelola = Pengelola::where('user_id', $user->id)->first();
            if ($pengelola) {
                $pengelola->update([
                    'paket_id'       => $request->paket_id ?? $pengelola->paket_id,
                    'nama_pengelola' => $request->nama_pengelola ?? $pengelola->nama_pengelola,
                    'email'          => $request->email_pengelola ?? $pengelola->email,
                    'telpon'         => $request->telpon_pengelola ?? $pengelola->telpon,
                    'alamat'         => $request->alamat_pengelola ?? $pengelola->alamat,
                    'logo'           => $request->logo ?? $pengelola->logo,
                    'deskripsi'      => $request->deskripsi_pengelola ?? $pengelola->deskripsi,
                ]);

                // Tangani perubahan logo jika ada
                if ($request->hasFile('logo')) {
                    // Hapus logo lama jika ada
                    if ($pengelola->logo && Storage::disk('local')->exists($pengelola->logo)) {
                        Storage::disk('local')->delete($pengelola->logo);
                    }

                    $file = $request->file('logo');
                    $fileName = time() . '_logo.' . $file->getClientOriginalExtension();
                    $path = 'private-file/logo/' . $fileName;
                    Storage::disk('local')->put($path, file_get_contents($file));
                    $pengelola->update(['logo' => $path]);
                }
            }

            // Perbarui data langganan untuk pengelola
            $langganan = Langganan::where('pengelola_id', $pengelola->id)->first();
            if ($langganan) {
                $langganan->update([
                    'paket_id'     => $request->paket_id ?? $langganan->paket_id,
                    'status'       => 'Tidak Aktif',
                    'mulai_langganan' => $request->mulai_langganan ?? $langganan->mulai_langganan,
                    'akhir_langganan' => $request->akhir_langganan ?? $langganan->akhir_langganan,
                ]);
            }
        }

        // Response setelah berhasil mengupdate
        return response()->json([
            'message' => 'User berhasil diperbarui',
            'user' => $user,
            'pengelola' => $pengelola ?? null,
            'langganan' => $langganan ?? null
        ]);
    }


    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        switch ($user->jabatan) {
            case 'Pengelola':
                $pengelola = Pengelola::where('user_id', $id)->first();

                if ($pengelola) {
                    // Mencari langganan pengelola
                    $langganan = Langganan::where('pengelola_id', $pengelola->id)->first();

                    if ($langganan) {
                        // Mengecek apakah ada pembayaran untuk langganan pengelola
                        $pembayaran_langganan = PembayaranLangganan::where('langganan_id', $langganan->id)->first();
                        if ($pembayaran_langganan) {
                            // Jika ada pembayaran, kembalikan peringatan
                            return response()->json(['message' => 'Pengelola ini memiliki riwayat pembayaran. Tidak dapat menghapus user.'], 400);
                        }

                        // Menghapus langganan jika tidak ada riwayat pembayaran
                        Langganan::where('pengelola_id', $pengelola->id)->delete();
                    }

                    // Menghapus data Pengelola setelah pengecekan riwayat pembayaran
                    Pengelola::where('user_id', $id)->delete();
                }

                // Menghapus gambar yang terkait dengan Pengelola (pictures, file_identitas, logo)
                if ($pengelola->logo && Storage::disk('local')->exists($pengelola->logo)) {
                    Storage::disk('local')->delete($pengelola->logo); // Hapus file logo
                }

                if ($pengelola->user->pictures && Storage::disk('local')->exists($pengelola->user->pictures)) {
                    Storage::disk('local')->delete($pengelola->user->pictures); // Hapus file pictures
                }

                if ($pengelola->user->file_identitas && Storage::disk('local')->exists($pengelola->user->file_identitas)) {
                    Storage::disk('local')->delete($pengelola->user->file_identitas); // Hapus file identitas
                }

                // Menghapus user (meskipun pengelola tidak ditemukan)
                User::where('id', $id)->delete();

                return response()->json(['message' => 'User dan data terkait berhasil dihapus!'], 200);
                break;


            case 'Pelanggan':

                //Pelanggan::where('user_id', $id)->delete();

                // Menghapus tagihan pelanggan jika ada
                // TagihanPelanggan::where('pelanggan_id', $user->pelanggan->id)->delete();
                break;

            case 'Staf':
                // Menghapus data Staf terkait user
                // Staf::where('user_id', $id)->delete();
                break;

            default:
                return response()->json(['message' => 'Unknown jabatan'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'User dan data terkait berhasil dihapus!'], 200);
    }
}

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

    private function getValidationRules($isUpdate = false, $id = null)
    {
        $rules = [
            'paket_id'         => 'sometimes|exists:tb_paket_langganan,id',
            'nama_lengkap'     => $isUpdate ? 'sometimes|string|max:50' : 'required|string|max:50',
            'username'         => $isUpdate ? 'sometimes|string|max:50|unique:tb_user,username,' . $id : 'required|string|max:50|unique:tb_user,username',
            'password'         => $isUpdate ? 'sometimes|nullable|string|min:6' : 'required|string|min:6',
            'email'            => $isUpdate ? 'sometimes|email|max:50|unique:tb_user,email,' . $id : 'required|email|max:50|unique:tb_user,email',
            'telpon'           => $isUpdate ? 'sometimes|string|max:15' : 'required|string|max:15',
            'jenis_identitas'  => $isUpdate ? 'sometimes|in:KTP,SIM,PASPOR,ID Lainnya' : 'required|in:KTP,SIM,PASPOR,ID Lainnya',
            'nomor_identitas'  => $isUpdate ? 'sometimes|string|max:20' : 'required|string|max:20',
            'file_identitas'   => $isUpdate ? 'nullable|image|mimes:jpg,jpeg,png|max:51200|unique:tb_user,file_identitas,' . $id : 'nullable|image|mimes:jpg,jpeg,png|max:51200|unique:tb_user,file_identitas',
            'alamat'           => $isUpdate ? 'sometimes|string' : 'required|string',
            'pictures'         => $isUpdate ? 'nullable|image|mimes:jpg,jpeg,png|max:51200|unique:tb_user,pictures,' . $id : 'nullable|image|mimes:jpg,jpeg,png|max:51200|unique:tb_user,pictures',
            'logo'             => $isUpdate ? 'nullable|image|mimes:jpg,jpeg,png|max:51200|unique:tb_user,logo,' . $id : 'nullable|image|mimes:jpg,jpeg,png|max:51200|unique:tb_user,logo',
            'jabatan'          => $isUpdate ? 'sometimes|in:Administrator,Pengelola,Pelanggan,Staf' : 'required|in:Administrator,Pengelola,Pelanggan,Staf',
            'nama_pengelola'   => 'sometimes|string|max:100',
            'email_pengelola'  => 'sometimes|email|max:50',
            'telpon_pengelola' => 'sometimes|string|max:15',
            'alamat_pengelola' => 'sometimes|string',
            'deskripsi_pengelola' => 'sometimes|string',
        ];
        return $rules;
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

        if ($status) {
            $query->where('jabatan', 'Pengelola');
            $users = $query->get();
            $filtered = [];
            foreach ($users as $user) {
                $row = $this->getUserData($user);
                if (isset($row['langganan']['status']) && $row['langganan']['status'] === $status) {
                    $filtered[] = $row;
                }
            }
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

    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules(false));
        $fileIdentitasPath = $this->handleFileUpload($request, 'file_identitas', 'private-file/identitas');
        $validated['file_identitas'] = $fileIdentitasPath ?? '';
        $picturesPath = $this->handleFileUpload($request, 'pictures', 'private-file/pictures');
        $validated['pictures'] = $picturesPath ?? '';
        $logoPath = $this->handleFileUpload($request, 'logo', 'private-file/logo');
        $validated['logo'] = $logoPath ?? '';
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $responseData = [
            'user' => $user,
            'pengelola' => null,
            'langganan' => null
        ];
        if ($validated['jabatan'] === 'Pengelola') {
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
            $logoPengelolaPath = $this->handleFileUpload($request, 'logo_pengelola', 'private-file/logo');
            if ($logoPengelolaPath) {
                $pengelola->update(['logo' => $logoPengelolaPath]);
            }
            $langganan = Langganan::create([
                'pengelola_id' => $pengelola->id,
                'status'       => 'Tidak Aktif',
                'paket_id'     => $request->paket_id,
                'user_id'      => 1,
                'mulai_langganan' => null,
                'akhir_langganan' => null
            ]);
            $responseData['pengelola'] = $pengelola;
            $responseData['langganan'] = $langganan;
        }
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

    private function handleFileUpload($request, $field, $folder, $oldFile = null)
    {
        if ($request->hasFile($field)) {
            try {
                if ($oldFile && Storage::disk('local')->exists($oldFile)) {
                    Storage::disk('local')->delete($oldFile);
                }
                $file = $request->file($field);
                $fileName = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                $path = $folder . '/' . $fileName;
                Storage::disk('local')->put($path, file_get_contents($file));
                return $path;
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return null;
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $validated = $request->validate($this->getValidationRules(true, $id));
        // Update ke tabel user
        $user->update($validated);
        $pengelola = null;
        if ($user->jabatan === 'Pengelola') {
            $pengelola = Pengelola::where('user_id', $id)->first();
            if ($pengelola) {
                if ($request->hasFile('logo')) {
                    $file = $request->file('logo');
                    $logoName = time() . '_' . $file->getClientOriginalExtension();
                    $file->storeAs('private-file/logo', $logoName);
                }

                $pengelolaUpdate = array_filter([
                    'paket_id'        => $validated['paket_id'] ?? null,
                    'nama_pengelola'  => $validated['nama_pengelola'] ?? null,
                    'email'           => $validated['email_pengelola'] ?? null,
                    'telpon'          => $validated['telpon_pengelola'] ?? null,
                    'alamat'          => $validated['alamat_pengelola'] ?? null,
                    'deskripsi'       => $validated['deskripsi_pengelola'] ?? null,
                    'logo'            => $logoName ?? null
                ]);

                $pengelola->update($pengelolaUpdate);
                return response()->json([
                    'message' => ' Berhasil diperbarui',
                    'user' => $user,
                    'pengelola' => $pengelola
                ]);
            }
        }
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
                    $langganan = Langganan::where('pengelola_id', $pengelola->id)->first();
                    if ($langganan) {
                        $pembayaran_langganan = PembayaranLangganan::where('langganan_id', $langganan->id)->first();
                        if ($pembayaran_langganan) {
                            return response()->json(['message' => 'Pengelola ini memiliki riwayat pembayaran. Tidak dapat menghapus user.'], 400);
                        }
                        Langganan::where('pengelola_id', $pengelola->id)->delete();
                    }
                    Pengelola::where('user_id', $id)->delete();
                }
                if ($pengelola->logo && Storage::disk('local')->exists($pengelola->logo)) {
                    Storage::disk('local')->delete($pengelola->logo);
                }
                if ($pengelola->user->pictures && Storage::disk('local')->exists($pengelola->user->pictures)) {
                    Storage::disk('local')->delete($pengelola->user->pictures);
                }
                if ($pengelola->user->file_identitas && Storage::disk('local')->exists($pengelola->user->file_identitas)) {
                    Storage::disk('local')->delete($pengelola->user->file_identitas);
                }
                User::where('id', $id)->delete();
                return response()->json(['message' => 'User dan data terkait berhasil dihapus!'], 200);
                break;
            case 'Pelanggan':
                break;
            case 'Staf':
                break;
            default:
                return response()->json(['message' => 'Unknown jabatan'], 400);
        }
        $user->delete();
        return response()->json(['message' => 'User dan data terkait berhasil dihapus!'], 200);
    }
}

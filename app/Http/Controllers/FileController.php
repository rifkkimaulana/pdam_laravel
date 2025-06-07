<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    public function showPrivateFile(Request $request, $folder, $filename)
    {
        // Cek hak akses user
        if (!$request->user()) {
            abort(403, 'Unauthorized');
        }

        $allowedFolders = ['bukti_bayar', 'identitas', 'logo', 'pictures'];
        if (!in_array($folder, $allowedFolders)) {
            abort(404);
        }

        $path = "private-file/{$folder}/{$filename}";
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->file(storage_path('app/' . $path));
    }
}

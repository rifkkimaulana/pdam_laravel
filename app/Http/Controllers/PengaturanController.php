<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PengaturanController extends Controller
{
    // GET semua pengaturan sistem
    public function index()
    {
        return response()->json([
            'app_name' => Config::get('app.name'),
            'app_env' => Config::get('app.env'),
            'app_debug' => Config::get('app.debug'),
            'timezone' => Config::get('app.timezone'),
            'locale' => Config::get('app.locale'),
        ]);
    }

    // PUT update pengaturan sistem
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'nullable|string|max:50',
            'timezone' => 'nullable|string',
            'locale' => 'nullable|string'
        ]);

        // Contoh menyimpan ke .env (pastikan konfigurasi diperbarui di Laravel setelah perubahan)
        if ($request->has('app_name')) {
            file_put_contents(base_path('.env'), str_replace(
                'APP_NAME=' . env('APP_NAME'),
                'APP_NAME=' . $request->app_name,
                file_get_contents(base_path('.env'))
            ));
        }

        return response()->json(['message' => 'Pengaturan berhasil diperbarui!']);
    }
}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\PaketPenggunaController;
use App\Http\Controllers\PenggunaanController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PaketLanggananController;
use App\Http\Controllers\PembayaranLanggananController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GangguanController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanggananController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PengelolaController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\BlokTarifController;
use App\Http\Controllers\StafController;
use App\Http\Controllers\FileController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('users', UserController::class);
    // Route::apiResource('paket', PaketPenggunaController::class);
    // Route::get('/paket', [PaketController::class, 'index']);
    // Route::get('/paket/{paket}/blok-tarif', [PaketController::class, 'blokTarif']);
    // Route::apiResource('blok-tarif', BlokTarifController::class);

    //Route::apiResource('penggunaan', PenggunaanController::class);
    //Route::apiResource('tagihan', TagihanController::class);
    //Route::apiResource('pembayaran', PembayaranController::class);
    //Route::post('/tagihan/generate-massal', [TagihanController::class, 'generateTagihanMassal']);
    //Route::post('/tagihan/cetak-massal',    [TagihanController::class, 'cetakTagihanMassal']);
    //Route::post('/tagihan/cetak-ulang', [TagihanController::class, 'cetakUlangTagihan']);
    //Route::apiResource('gangguan', GangguanController::class);
    //Route::apiResource('penugasan', PenugasanController::class);

    Route::apiResource('pembayaran-langganan', PembayaranLanggananController::class);
    Route::apiResource('pengelola', PengelolaController::class);

    // Route::apiResource('pelanggan', PelangganController::class);
    // Route::apiResource('dashboard', DashboardController::class);
    // Route::apiResource('pengaturan', PengaturanController::class);
    //  Route::apiResource('staf', StafController::class);

    // Route::apiResource('langganan', LanggananController::class);
    Route::apiResource('paket-langganan', PaketLanggananController::class);

    Route::get('/private-file/{folder}/{filename}', [FileController::class, 'showPrivateFile'])
        ->where('folder', 'bukti_bayar|identitas|logo|pictures')
        ->name('private-file.show');
});

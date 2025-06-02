<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// import controllers
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


// Route Api resource
Route::apiResource('users', UserController::class);
Route::apiResource('paket', PaketPenggunaController::class);
Route::apiResource('penggunaan', PenggunaanController::class);

// tagihan dan pembayaran
Route::apiResource('tagihan', TagihanController::class);
Route::apiResource('pembayaran', PembayaranController::class);

// generate & cetak tagihan pelanggan
Route::post('/tagihan/generate-massal', [TagihanController::class, 'generateTagihanMassal']);
Route::post('/tagihan/cetak-massal',    [TagihanController::class, 'cetakTagihanMassal']);
Route::post('/tagihan/cetak-ulang', [TagihanController::class, 'cetakUlangTagihan']);


// gangguan dan penugasan
Route::apiResource('gangguan', GangguanController::class);
Route::apiResource('penugasan', PenugasanController::class);

// Routes SaaS Pelanggan 
Route::apiResource('paket-langganan', PaketLanggananController::class);
Route::apiResource('langganan', LanggananController::class);
Route::apiResource('pembayaran-langganan', PembayaranLanggananController::class);

Route::apiResource('pelanggan', PelangganController::class);

Route::apiResource('dashboard', DashboardController::class);
Route::apiResource('pengaturan', PengaturanController::class);


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

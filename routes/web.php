<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\AbsenController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\jabatanController;
use App\Http\Controllers\karyawanController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\RekapDataController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [authController::class, 'index'])->name('login')->middleware('guest');
Route::get('/register', [authController::class, 'register'])->middleware('guest');
Route::post('/register-proses', [authController::class, 'registerProses'])->middleware('guest');
Route::post('/login-proses', [authController::class, 'loginProses'])->middleware('guest');
Route::get('/dashboard', [dashboardController::class, 'index'])->middleware('admin');
Route::post('/logout', [authController::class, 'logout'])->middleware('auth');
Route::get('/karyawan', [karyawanController::class, 'index'])->middleware('admin');
Route::get('/karyawan/tambah-karyawan', [karyawanController::class, 'tambahKaryawan'])->middleware('admin');
Route::post('/karyawan/tambah-karyawan-proses', [karyawanController::class, 'tambahKaryawanProses'])->middleware('admin');
Route::get('/karyawan/detail/{id}', [karyawanController::class, 'detail'])->middleware('admin');
Route::put('/karyawan/proses-edit/{id}', [karyawanController::class, 'editKaryawanProses'])->middleware('admin');
Route::delete('/karyawan/delete/{id}', [karyawanController::class, 'deleteKaryawan'])->middleware('admin');
Route::get('/karyawan/edit-password/{id}', [karyawanController::class, 'editPassword'])->middleware('admin');
Route::put('/karyawan/edit-password-proses/{id}', [karyawanController::class, 'editPasswordProses'])->middleware('admin');
Route::resource('/shift', ShiftController::class)->middleware('admin');
Route::get('/karyawan/shift/{id}', [karyawanController::class, 'shift'])->middleware('admin');
Route::post('/karyawan/shift/proses-tambah-shift', [karyawanController::class, 'prosesTambahShift'])->middleware('admin');
Route::delete('/karyawan/delete-shift/{id}', [karyawanController::class, 'deleteShift'])->middleware('admin');
Route::get('/karyawan/edit-shift/{id}', [karyawanController::class, 'editShift'])->middleware('admin');
Route::put('/karyawan/proses-edit-shift/{id}', [karyawanController::class, 'prosesEditShift'])->middleware('auth');
Route::get('/absen', [AbsenController::class, 'index'])->middleware('auth');
Route::get('/my-location', [AbsenController::class, 'myLocation'])->middleware('auth');
Route::put('/absen/masuk/{id}', [AbsenController::class, 'absenMasuk'])->middleware('auth');
Route::put('/absen/pulang/{id}', [AbsenController::class, 'absenPulang'])->middleware('auth');
Route::get('/data-absen', [AbsenController::class, 'dataAbsen'])->middleware('admin');
Route::get('/data-absen/{id}/edit-masuk', [AbsenController::class, 'editMasuk'])->middleware('admin');
Route::get('/maps/{lat}/{long}/{userid}', [AbsenController::class, 'maps'])->middleware('auth');
Route::put('/data-absen/{id}/proses-edit-masuk', [AbsenController::class, 'prosesEditMasuk'])->middleware('admin');
Route::get('/data-absen/{id}/edit-pulang', [AbsenController::class, 'editPulang'])->middleware('admin');
Route::put('/data-absen/{id}/proses-edit-pulang', [AbsenController::class, 'prosesEditPulang'])->middleware('admin');
Route::delete('/data-absen/{id}/delete', [AbsenController::class, 'deleteAdmin'])->middleware('admin');
Route::get('/my-absen', [AbsenController::class, 'myAbsen'])->middleware('auth');
Route::get('/lembur', [LemburController::class, 'index'])->middleware('auth');
Route::post('/lembur/masuk', [LemburController::class, 'masuk'])->middleware('auth');
Route::put('/lembur/pulang/{id}', [LemburController::class, 'pulang'])->middleware('auth');
Route::get('/data-lembur', [LemburController::class, 'dataLembur'])->middleware('admin');
Route::get('/my-lembur', [LemburController::class, 'myLembur'])->middleware('auth');
Route::get('/rekap-data', [RekapDataController::class, 'index'])->middleware('admin');
Route::get('/cuti', [CutiController::class, 'index'])->middleware('auth');
Route::post('/cuti/tambah', [CutiController::class, 'tambah'])->middleware('auth');
Route::delete('/cuti/delete/{id}', [CutiController::class, 'delete'])->middleware('auth');
Route::get('/cuti/edit/{id}', [CutiController::class, 'edit'])->middleware('auth');
Route::put('/cuti/proses-edit/{id}', [CutiController::class, 'editProses'])->middleware('auth');
Route::get('/data-cuti', [CutiController::class, 'dataCuti'])->middleware('admin');
Route::get('/data-cuti/tambah', [CutiController::class, 'tambahAdmin'])->middleware('admin');
Route::post('/data-cuti/getuserid', [CutiController::class, 'getUserId'])->middleware('admin');
Route::post('/data-cuti/proses-tambah', [CutiController::class, 'tambahAdminProses'])->middleware('admin');
Route::delete('/data-cuti/delete/{id}', [CutiController::class, 'deleteAdmin'])->middleware('admin');
Route::get('/data-cuti/edit/{id}', [CutiController::class, 'editAdmin'])->middleware('admin');
Route::put('/data-cuti/edit-proses/{id}', [CutiController::class, 'editAdminProses'])->middleware('admin');
Route::get('/my-profile', [KaryawanController::class, 'myProfile'])->middleware('auth');
Route::put('/my-profile/update/{id}', [KaryawanController::class, 'myProfileUpdate'])->middleware('auth');
Route::get('/my-profile/edit-password', [KaryawanController::class, 'editPassMyProfile'])->middleware('auth');
Route::put('/my-profile/edit-password-proses/{id}', [KaryawanController::class, 'editPassMyProfileProses'])->middleware('auth');
Route::get('/lokasi-kantor', [LokasiController::class, 'index'])->middleware('admin');
Route::get('/lokasi-kantor/tambah', [LokasiController::class, 'tambahLokasi'])->middleware('admin');
Route::post('/lokasi-kantor/tambah-proses', [LokasiController::class, 'prosesTambahLokasi'])->middleware('admin');
Route::get('/lokasi-kantor/edit/{id}', [LokasiController::class, 'editLokasi'])->middleware('admin');
Route::put('/lokasi-kantor/update/{id}', [LokasiController::class, 'updateLokasi'])->middleware('admin');
Route::put('/lokasi-kantor/radius/{id}', [LokasiController::class, 'updateRadiusLokasi'])->middleware('admin');
Route::delete('/lokasi-kantor/delete/{id}', [LokasiController::class, 'deleteLokasi'])->middleware('admin');
Route::get('/reset-cuti', [KaryawanController::class, 'resetCuti'])->middleware('admin');
Route::put('/reset-cuti/{id}', [KaryawanController::class, 'resetCutiProses'])->middleware('admin');
Route::get('/jabatan', [jabatanController::class, 'index'])->middleware('admin');
Route::get('/jabatan/create', [jabatanController::class, 'create'])->middleware('admin');
Route::post('/jabatan/insert', [jabatanController::class, 'insert'])->middleware('admin');
Route::get('/jabatan/edit/{id}', [jabatanController::class, 'edit'])->middleware('admin');
Route::put('/jabatan/update/{id}', [jabatanController::class, 'update'])->middleware('admin');
Route::delete('/jabatan/delete/{id}', [jabatanController::class, 'delete'])->middleware('admin');
Route::get('/dokumen', [DokumenController::class, 'index'])->middleware('admin');
Route::get('/dokumen/tambah', [DokumenController::class, 'tambah'])->middleware('admin');
Route::post('/dokumen/tambah-proses', [DokumenController::class, 'tambahProses'])->middleware('admin');
Route::get('/dokumen/edit/{id}', [DokumenController::class, 'edit'])->middleware('admin');
Route::put('/dokumen/edit-proses/{id}', [DokumenController::class, 'editProses'])->middleware('admin');
Route::delete('/dokumen/delete/{id}', [DokumenController::class, 'delete'])->middleware('admin');
Route::get('/my-dokumen', [DokumenController::class, 'myDokumen'])->middleware('auth');
Route::get('/my-dokumen/tambah', [DokumenController::class, 'myDokumenTambah'])->middleware('auth');
Route::post('/my-dokumen/tambah-proses', [DokumenController::class, 'myDokumenTambahProses'])->middleware('auth');
Route::get('/my-dokumen/edit/{id}', [DokumenController::class, 'myDokumenEdit'])->middleware('auth');
Route::put('/my-dokumen/edit-proses/{id}', [DokumenController::class, 'myDokumenEditProses'])->middleware('auth');
Route::delete('/my-dokumen/delete/{id}', [DokumenController::class, 'myDokumenDelete'])->middleware('auth');








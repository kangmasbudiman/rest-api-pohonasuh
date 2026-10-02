<?php

use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
/*firebase*/
Route::post('sendNotification', [ApiController::class, 'sendNotification']);
Route::post('saveToken', [ApiController::class, 'saveToken']);
Route::post('sendNotification', [ApiController::class, 'sendNotification']);
/*end*/



Route::post('mytreesgroup', [ApiController::class, 'mytreesgroup']);
Route::post('mycertificate', [ApiController::class, 'mycertificate']);
Route::get('users', [UsersController::class, 'index']);
Route::get('getdesa', [ApiController::class, 'getdesa']);
Route::post('tambahlokasi', [ApiController::class, 'tambahlokasi']);
Route::post('editlokasi', [ApiController::class, 'editlokasi']);
Route::post('hapuslokasi', [ApiController::class, 'hapuslokasi']);
Route::post('getprofil', [ApiController::class, 'getprofil']);
Route::get('tes', [ApiController::class, 'tes']);
Route::post('tambah-users', [UsersController::class, 'store']);
Route::post('loginuser', [ApiController::class, 'loginuser']);
Route::post('register', [ApiController::class, 'register']);
Route::get('blog', [ApiController::class, 'blog']);
Route::post('updateduration', [ApiController::class, 'updateduration']);
Route::post('pohon', [ApiController::class, 'pohon']);
Route::post('pohoncari', [ApiController::class, 'pohoncari']);
Route::post('addfototaging', [ApiController::class, 'addfototaging']);
Route::post('uploadfototaging', [ApiController::class, 'uploadfototaging']);
Route::post('updateposisi', [ApiController::class, 'updateposisi']);
Route::get('posisipetugas', [ApiController::class, 'posisipetugas']);
Route::get('getkontak', [ApiController::class, 'getkontak']);
Route::post('updatekontak', [ApiController::class, 'updatekontak']);
Route::post('updatedevicetoken', [ApiController::class, 'updatedevicetoken']);
Route::get('penugasandesa', [ApiController::class, 'penugasandesa']);
Route::post('updatedesapetugas', [ApiController::class, 'updatedesapetugas']);
Route::post('pohonmapdesa', [ApiController::class, 'pohonmapdesa']);
Route::get('getmembers', [ApiController::class, 'getmembers']);
Route::post('updatestatusmember', [ApiController::class, 'updatestatusmember']);
Route::get('allcertificate', [ApiController::class, 'allcertificate']);
Route::get('adopsilist', [ApiController::class, 'adopsilist']);
Route::get('reportprice', [ApiController::class, 'reportprice']);
Route::post('editadopsi', [ApiController::class, 'editadopsi']);
Route::post('hapusadopsi', [ApiController::class, 'hapusadopsi']);
/* Backup database — wajib header X-Backup-Token (env BACKUP_TOKEN) */
Route::get('backuplist', [ApiController::class, 'backuplist']);
Route::post('backupcreate', [ApiController::class, 'backupcreate']);
Route::get('backupfile', [ApiController::class, 'backupfile']);
Route::post('backupdelete', [ApiController::class, 'backupdelete']);
Route::post('globalsearch', [ApiController::class, 'globalsearch']);


Route::post('pohonmap', [ApiController::class, 'pohonmap']);
Route::get('pohonmapall', [ApiController::class, 'pohonmapall']);
Route::get('pohonhighlight', [ApiController::class, 'pohonhighlight']);
Route::get('slider', [ApiController::class, 'slider']);
Route::get('blogfirst', [ApiController::class, 'blogfirst']);
Route::post('addviewer', [ApiController::class, 'addviewer']);
Route::post('filtertrees', [ApiController::class, 'filtertrees']);
Route::post('getpohondesa', [ApiController::class, 'getpohondesa']);
Route::post('getpohondesafilter', [ApiController::class, 'getpohondesafilter']);
Route::get('getrekening', [ApiController::class, 'getrekening']);
Route::post('tobasket', [ApiController::class, 'tobasket']);
Route::get('getnoadmin', [ApiController::class, 'getnoadmin']);
Route::post('tambahnoadmin', [ApiController::class, 'tambahnoadmin']);
Route::post('editnoadmin', [ApiController::class, 'editnoadmin']);
Route::post('hapusnoadmin', [ApiController::class, 'hapusnoadmin']);
Route::post('adopsi', [ApiController::class, 'adopsi']);
Route::post('mytrees', [ApiController::class, 'mytrees']);
Route::post('sendNotif', [ApiController::class, 'sendNotif']);
Route::post('sendNotifkepengurus', [ApiController::class, 'sendNotifkepengurus']);
Route::get('ordercustomer', [ApiController::class, 'ordercustomer']);
Route::post('updatestatusproses', [ApiController::class, 'updatestatusproses']);
Route::post('updatestatuscomplate', [ApiController::class, 'updatestatuscomplate']);
Route::post('ceklatlangpohon', [ApiController::class, 'ceklatlangpohon']);
Route::post('uploadimage', [ApiController::class, 'uploadimage']);
Route::post('hapuspohonimage', [ApiController::class, 'hapuspohonimage']);
Route::post('getdetailimage', [ApiController::class, 'getdetailimage']);
Route::post('updateforcertificate', [ApiController::class, 'updateforcertificate']);
Route::post('testjson', [ApiController::class, 'testjson']);
Route::post('pohonimage', [ApiController::class, 'pohonimage']);
Route::post('blogbyfilter', [ApiController::class, 'blogbyfilter']);
Route::get('blogbyid', [ApiController::class, 'blogbyid']);
Route::post('databasket', [ApiController::class, 'databasket']);
Route::post('gettroley', [ApiController::class, 'gettroley']);
Route::post('mytrolley', [ApiController::class, 'mytrolley']);
Route::post('mytrolleydelete', [ApiController::class, 'mytrolleydelete']);
Route::post('mypesandelete', [ApiController::class, 'mypesandelete']);
Route::post('mytrolleygrandtotal', [ApiController::class, 'mytrolleygrandtotal']);
Route::post('confirmasipembayaran', [ApiController::class, 'confirmasipembayaran']);
Route::post('getconfirmasi', [ApiController::class, 'getconfirmasi']);
Route::post('uploadbuktitransfer', [ApiController::class, 'uploadbuktitransfer']);
Route::post('verivication', [ApiController::class, 'verivication']);
Route::post('batalverivication', [ApiController::class, 'batalverivication']);
/*mayar payment gateway*/
Route::post('createinvoice', [ApiController::class, 'createinvoice']);
Route::post('webhookmayar', [ApiController::class, 'webhookmayar']);
Route::post('updatememo', [ApiController::class, 'updatememo']);
Route::post('orderbyinvoice', [ApiController::class, 'orderbyinvoice']);
Route::post('hapustransaksitidakjadi', [ApiController::class, 'hapustransaksitidakjadi']);
Route::post('pesanNotif', [ApiController::class, 'pesanNotif']);
Route::post('getPesanku', [ApiController::class, 'getPesanku']);
Route::post('listPesanku', [ApiController::class, 'listPesanku']);
Route::post('mypesanupdate', [ApiController::class, 'mypesanupdate']);
Route::post('setPohonterbaik', [ApiController::class, 'setPohonterbaik']);
Route::post('setPohonhighlight', [ApiController::class, 'setPohonhighlight']);
Route::post('pohonbydesa', [ApiController::class, 'pohonbydesa']);
Route::post('setPohonremove', [ApiController::class, 'setPohonremove']);
Route::post('ordercustomerbypengurus', [ApiController::class, 'ordercustomerbypengurus']);
Route::post('reportdesa', [ApiController::class, 'reportdesa']);
Route::post('updatenoted', [ApiController::class, 'updatenoted']);
Route::post('getnoted', [ApiController::class, 'getnoted']);
Route::post('feedesa', [ApiController::class, 'feedesa']);
Route::post('lihatfototaging', [ApiController::class, 'lihatfototaging']);
/*integrasi website*/
Route::post('pohonbykode', [ApiController::class, 'pohonbykode']);
Route::get('sertifikatpublik/{certnum}', [ApiController::class, 'sertifikatpublik'])
  ->where('certnum', '.*'); // certnum mengandung '/' (mis. 001/LPHD-RA/2026)
Route::get('totaldonasi', [ApiController::class, 'totaldonasi']);
Route::post('tambahpohon', [ApiController::class, 'tambahpohon']);
Route::post('uploadcover', [ApiController::class, 'uploadcover']);
Route::post('tambahblog', [ApiController::class, 'tambahblog']);
Route::post('editblog', [ApiController::class, 'editblog']);
Route::post('hapusblog', [ApiController::class, 'hapusblog']);
Route::post('editpohon', [ApiController::class, 'editpohon']);
Route::post('hapuspohon', [ApiController::class, 'hapuspohon']);
Route::post('tambahslider', [ApiController::class, 'tambahslider']);
Route::post('editslider', [ApiController::class, 'editslider']);
Route::post('hapusslider', [ApiController::class, 'hapusslider']);
// Fitur adopsi lindungihutan.com (2026-09-26)
Route::get('statistikdampak', [ApiController::class, 'statistikdampak']);
Route::get('specieslist', [ApiController::class, 'specieslist']);
Route::get('speciesdetail/{id}', [ApiController::class, 'speciesdetail']);
Route::post('tambahspecies', [ApiController::class, 'tambahspecies']);
Route::post('editspecies', [ApiController::class, 'editspecies']);
Route::post('hapusspecies', [ApiController::class, 'hapusspecies']);
Route::get('testimonilist', [ApiController::class, 'testimonilist']);
Route::post('tambahtestimoni', [ApiController::class, 'tambahtestimoni']);
Route::post('edittestimoni', [ApiController::class, 'edittestimoni']);
Route::post('hapustestimoni', [ApiController::class, 'hapustestimoni']);
Route::get('partnerlist', [ApiController::class, 'partnerlist']);
Route::post('tambahpartner', [ApiController::class, 'tambahpartner']);
Route::post('editpartner', [ApiController::class, 'editpartner']);
Route::post('hapuspartner', [ApiController::class, 'hapuspartner']);







Route::get('getshift', [ApiController::class, 'getshift']);
Route::get('getketegori_service', [ApiController::class, 'getketegori_service']);








Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

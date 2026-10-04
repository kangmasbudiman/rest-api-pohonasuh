<?php

namespace App\Http\Controllers\API;

use App\Models\Kategori_service;
use Exception;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shift;
use App\Models\Member;
use App\Models\PosisiPetugas;
use App\Models\DesaPetugas;
use App\Models\Kontak;
use App\Models\Blog;
use App\Models\Slider;
use App\Models\Desa;
use App\Models\Pohon;
use App\Models\Rekening;
use App\Models\Tobasket;
use App\Models\Noadmin;
use App\Models\Dataadopsi;
use App\Models\Detailimage;
use App\Models\Pohonimage;
use App\Models\Testjson;
use App\Models\Confirmasi;
use App\Models\Pesan;
use App\Models\Fototaging;
use App\Models\SpeciesCatalog;
use App\Models\Testimoni;
use App\Models\Partner;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;


class ApiController extends Controller
{
     protected $messaging;
     
     
      public function __construct()
    {
        // Inisialisasi Firebase Messaging
        $firebase = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $firebase->createMessaging();
    }
    
    
    
    public function saveToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fcm_token' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json(['message' => 'Token FCM berhasil disimpan'], 200);
    }

    // Fungsi untuk mendapatkan token FCM user berdasarkan user_id
    public function getToken($user_id)
    {
        $user = User::find($user_id);

        if (!$user || !$user->fcm_token) {
            return response()->json(['message' => 'Token tidak ditemukan'], 404);
        }

        return response()->json(['fcm_token' => $user->fcm_token], 200);
    }
    
    
    
    
    
    
    
    
    
    
    public function sendNotification(Request $request)
    {



    
        $request->validate([
            'token' => 'required',
            'title' => 'required|string',
            'body'  => 'required|string',
        ]);

        $token = $request->token;
        $title = $request->title;
        $body  = $request->body;

        try {
            // Buat pesan notifikasi
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body));

            // Kirim notifikasi
            $this->messaging->send($message);

            return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dikirim!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengirim notifikasi', 'error' => $e->getMessage()], 500);
        }

    }

    // Push FCM (HTTP v1 via Kreait) ke member berdasarkan id_token di tabel
    // member. Diam-diam gagal: notifikasi in-app (pesan_notif) tetap tersimpan.
    private function kirimPush($idmember, $title, $body){
        try {
            $m = Member::find($idmember);
            if (!$m || !$m->id_token) { return; }
            $message = CloudMessage::withTarget('token', $m->id_token)
                ->withNotification(Notification::create($title, $body))
                ->withData(['title' => $title, 'body' => $body]);
            $this->messaging->send($message);
        } catch (\Exception $e) {
            Log::info('kirimPush gagal idmember='.$idmember.' : '.$e->getMessage());
        }
    }

    // Pesan + push ke semua admin saat member check out order. Dulu tugas
    // endpoint sendNotif, tapi app tak pernah memanggilnya — kini melekat
    // di alur confirmasipembayaran supaya notifikasi pasti terkirim.
    private function notifikasiCheckoutAdmin($idmember){
        $m = Member::find($idmember);
        $namauser = $m ? $m->name : '';
        foreach (Member::where('admin',1)->get() as $k) {
            $isip = "Hallo Admin Pohon Asuh ada Order Check Out pohon dari Nama: {$namauser} ";
            $pesan = new Pesan();
            $pesan->idmember = $k->id;
            $pesan->pesan = $isip;
            $pesan->status = "noread";
            $pesan->save();
            $this->kirimPush($k->id, 'CheckOut Trees', $isip);
        }
    }

    // Registrasi / refresh token FCM device dari app tanpa login ulang.
    public function updatedevicetoken(Request $request){
        $id = $request->input('id');
        $token = $request->input('id_token');
        if ($id === null || $token === null || $token === '') {
            return response()->json(['value' => '400', 'pesan' => 'id dan id_token wajib diisi'], 400);
        }
        $m = Member::find($id);
        if (!$m) {
            return response()->json(['value' => '404', 'pesan' => 'member tidak ditemukan'], 404);
        }
        $m->id_token = $token;
        $m->update();
        return response()->json(['value' => '200', 'pesan' => 'Token diperbarui']);
    }
    
    
    
    
    
    
    
    
    public function lihatfototaging(Request $request){
      $data=Fototaging::where('idpohon',$request->idpohon)->get();
      if(count($data)>0){
    $items = array();
    foreach ($data as $k) {
      $b['urlGambar'] = $k->urlGambar;
     
      array_push($items, $b);
    }
    return response()->json($items);
  }else {
    $items = array();
    array_push($items);
    return response()->json($items);
  }
  }
    
    
    
    
public function addfototaging(Request $request){

    $data=new Fototaging();

    $data->idpohon=$request->input("idpohon");
    $data->tanggal=$request->input("tanggal");
    $data->idmember=$request->input("idmember");
    $data->idadopsi=$request->input("idadopsi");
    $data->urlGambar=$request->input("urlGambar");
    $data->save();

     return response()->json([
                'value'=>'200',
                'pesan' =>"Success",
        ]);

}

// Upload foto taging langsung (multipart): file disimpan di server,
// row foto_tagging diisi URL self-hosted (tanpa Supabase).
public function uploadfototaging(Request $request){
    if (!$request->hasFile('image')) {
        return response()->json([
            'value'=>'400',
            'pesan'=>'No file uploaded',
        ], 400);
    }

    $file=$request->file('image');
    $name_file='taging_'.time().'_'.strtoupper(substr(md5(uniqid(rand(), true)), 0, 6)).'.'.$file->getClientOriginalExtension();
    $destinationPath=base_path('public/upload/taging');
    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0775, true);
    }
    $file->move($destinationPath, $name_file);

    $url=$request->getSchemeAndHttpHost().str_replace('/index.php', '', $request->getBaseUrl()).'/upload/taging/'.$name_file;

    $data=new Fototaging();
    $data->idpohon=$request->input('idpohon');
    $data->tanggal=$request->input('tanggal');
    $data->idmember=$request->input('idmember');
    $data->idadopsi=$request->input('idadopsi');
    $data->urlGambar=$url;
    $data->save();

    return response()->json([
        'value'=>'200',
        'pesan'=>'Success',
        'url'=>$url,
    ]);
}

// Kontak dinamos Contact Us: satu baris (id=1), di-set admin dari app.
public function getkontak(){
    $k = Kontak::find(1);
    return response()->json([
        'nama' => $k->nama ?? '',
        'telepon' => $k->telepon ?? '',
        'whatsapp' => $k->whatsapp ?? '',
        'email' => $k->email ?? '',
    ]);
}

public function updatekontak(Request $request){
    Kontak::updateOrCreate(
        ['id' => 1],
        [
            'nama' => $request->input('nama') ?? '',
            'telepon' => $request->input('telepon') ?? '',
            'whatsapp' => $request->input('whatsapp') ?? '',
            'email' => $request->input('email') ?? '',
        ]
    );
    return response()->json([
        'value' => '200',
        'pesan' => 'Kontak diperbarui',
    ]);
}

// Pelacakan posisi petugas taging: upsert per idmember.
public function updateposisi(Request $request){
    $idmember=$request->input('idmember');
    $lat=$request->input('lat');
    $lng=$request->input('lng');
    if ($idmember===null || $lat===null || $lng===null) {
        return response()->json([
            'value'=>'400',
            'pesan'=>'idmember, lat, lng wajib diisi',
        ], 400);
    }

    $posisi = PosisiPetugas::updateOrCreate(
        ['idmember'=>$idmember],
        ['lat'=>$lat, 'lng'=>$lng]
    );
    // Koordinat sama (GPS stabil) → tak ada atribut dirty → Eloquent
    // melompati UPDATE dan updated_at tak berubah. touch() memaksa
    // refresh timestamp agar admin tahu petugas masih aktif mengirim.
    $posisi->touch();

    return response()->json([
        'value'=>'200',
        'pesan'=>'Success',
    ]);
}

// Data penugasan petugas per desa + daftar petugas (halaman admin).
// Satu desa boleh banyak petugas (pivot desa_petugas), satu petugas boleh banyak desa.
public function penugasandesa(){
    $desaRows = Desa::orderBy('nama')->get(['id','nama','provinsi','foto']);
    $petugasRows = Member::where('admin',2)->orderBy('name')->get(['id','name','emaile']);

    $petugasById = array();
    foreach ($petugasRows as $p) {
        $petugasById[$p->id] = $p;
    }

    $perDesa = array();
    $perPetugas = array();
    foreach (DesaPetugas::all() as $pv) {
        $perDesa[$pv->iddesa][] = $pv->idpetugas;
        $perPetugas[$pv->idpetugas][] = $pv->iddesa;
    }
    $namaDesaById = array();
    foreach ($desaRows as $d) {
        $namaDesaById[$d->id] = $d->nama;
    }

    $desa = array();
    foreach ($desaRows as $d) {
        $b['iddesa'] = $d->id;
        $b['nama'] = $d->nama;
        $b['provinsi'] = $d->provinsi;
        $b['foto'] = $d->foto;
        $list = array();
        if (isset($perDesa[$d->id])) {
            foreach ($perDesa[$d->id] as $pid) {
                $p = isset($petugasById[$pid]) ? $petugasById[$pid] : null;
                if ($p) {
                    array_push($list, [
                        'idpetugas' => $p->id,
                        'nama' => $p->name,
                        'email' => $p->emaile,
                    ]);
                }
            }
        }
        $b['petugas'] = $list;
        $b['jmlpetugas'] = count($list);
        array_push($desa, $b);
    }

    $petugas = array();
    foreach ($petugasRows as $p) {
        $milik = isset($perPetugas[$p->id]) ? $perPetugas[$p->id] : array();
        $namaMilik = array();
        foreach ($milik as $iddesa) {
            if (isset($namaDesaById[$iddesa])) {
                array_push($namaMilik, $namaDesaById[$iddesa]);
            }
        }
        $b2['idmember'] = $p->id;
        $b2['nama'] = $p->name;
        $b2['email'] = $p->emaile;
        $b2['jumdesa'] = count($namaMilik);
        $b2['desa_list'] = implode(', ', $namaMilik);
        array_push($petugas, $b2);
    }

    return response()->json(['desa'=>$desa, 'petugas'=>$petugas]);
}

// Sinkron daftar petugas satu desa (idpetugas = 'id1,id2,...' atau '' utk lepas semua).
public function updatedesapetugas(Request $request){
    $iddesa=$request->input('iddesa');
    $idpetugasRaw=$request->input('idpetugas');
    if ($iddesa===null) {
        return response()->json([
            'value'=>'400',
            'pesan'=>'iddesa wajib diisi',
        ], 400);
    }
    $desa=Desa::find($iddesa);
    if (!$desa) {
        return response()->json([
            'value'=>'404',
            'pesan'=>'Desa tidak ditemukan',
        ], 404);
    }

    $ids=array();
    foreach (explode(',', (string)$idpetugasRaw) as $v) {
        $v=trim($v);
        if ($v!=='') {
            array_push($ids, $v);
        }
    }
    $ids=array_values(array_unique($ids));
    foreach ($ids as $id) {
        $member=Member::where([['id',$id],['admin',2]])->first();
        if (!$member) {
            return response()->json([
                'value'=>'404',
                'pesan'=>'Petugas tidak ditemukan',
            ], 404);
        }
    }

    DesaPetugas::where('iddesa',$iddesa)->delete();
    foreach ($ids as $id) {
        DesaPetugas::create(['iddesa'=>$iddesa, 'idpetugas'=>$id]);
    }

    return response()->json([
        'value'=>'200',
        'pesan'=>'Success',
    ]);
}

// Daftar posisi terakhir semua petugas (member admin=2).
public function posisipetugas(){
    $rows=PosisiPetugas::join('member', 'member.id', '=', 'posisi_petugas.idmember')
        ->where('member.admin', 2)
        ->get(['posisi_petugas.idmember', 'member.name as nama', 'member.emaile as email', 'posisi_petugas.lat', 'posisi_petugas.lng', 'posisi_petugas.updated_at']);

    $items=array();
    foreach ($rows as $r) {
        $b['idmember']=$r->idmember;
        $b['nama']=$r->nama;
        $b['email']=$r->email;
        $b['lat']=$r->lat;
        $b['lng']=$r->lng;
        $b['updated_at']=$r->updated_at;
        array_push($items, $b);
    }
    return response()->json($items);
}

// Seluruh pohon satu desa (semua status) — data minimum utk peta offline tagging.
public function pohonmapdesa(Request $request){
    $desa=$request->input('desa');

    $data=Pohon::where('desa', $desa)
        ->orderBy('idpohon')
        ->get(['id', 'idpohon', 'localname', 'species', 'latitude', 'longitude', 'adopted']);

    return response()->json($data);
}

// Daftar seluruh member utk kelola user di web admin.
public function getmembers(){
    $members=Member::orderByDesc('id')
        ->get(['id', 'name', 'emaile', 'hp', 'admin', 'aktif', 'tanggal', 'address']);

    return response()->json($members);
}

// Aktifkan / nonaktifkan member (login ditolak bila aktif=0).
public function updatestatusmember(Request $request){
    $idmember=$request->input('idmember');
    $idadmin=$request->input('idadmin');
    $aktif=$request->input('aktif');

    if ($idmember===null || $aktif===null) {
        return response()->json([
            'value'=>'400',
            'pesan'=>'idmember dan aktif wajib diisi',
        ], 400);
    }
    if ((string)$idmember === (string)$idadmin) {
        return response()->json([
            'value'=>'400',
            'pesan'=>'Tidak dapat mengubah status akun sendiri',
        ], 400);
    }
    if (!in_array((string)$aktif, ['0', '1'], true)) {
        return response()->json([
            'value'=>'400',
            'pesan'=>'aktif hanya boleh 0 atau 1',
        ], 400);
    }

    $member=Member::find($idmember);
    if (!$member) {
        return response()->json([
            'value'=>'404',
            'pesan'=>'Member tidak ditemukan',
        ], 404);
    }

    $member->aktif=(int)$aktif;
    $member->update();

    return response()->json([
        'value'=>'200',
        'pesan'=>'Success',
    ]);
}

// Seluruh sertifikat (data_adopsi ber-certnum) utk kelola sertifikat web admin.
// idpohon di data_pohon tidak unik (ada duplikat) → GROUP BY data_adopsi.id
// agar satu sertifikat tak muncul dua kali; leftJoin utk pohon yg sudah dihapus.
public function allcertificate(){
    $rows=Dataadopsi::join('member', 'member.id', '=', 'data_adopsi.pengasuh')
        ->leftJoin('data_pohon', 'data_pohon.idpohon', '=', 'data_adopsi.idpohon')
        ->whereNotNull('data_adopsi.certnum')
        ->where('data_adopsi.certnum', '!=', '')
        ->groupBy('data_adopsi.id')
        ->orderByDesc('data_adopsi.id')
        ->selectRaw('data_adopsi.id, data_adopsi.certnum, member.name as nama, data_adopsi.idpohon, MIN(data_pohon.localname) as localname, data_adopsi.invoice, data_adopsi.tgl_adopt, data_adopsi.tgl_exp, data_adopsi.proses, MIN(data_pohon.desa) as desa')
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'certnum' => $r->certnum,
                'nama' => $r->nama,
                'idpohon' => $r->idpohon,
                'localname' => $r->localname ?? '-',
                'invoice' => $r->invoice,
                'tgl_adopt' => $r->tgl_adopt,
                'tgl_exp' => $r->tgl_exp,
                'proses' => $r->proses,
                'desa' => $r->desa ?? '-',
            ];
        });

    return response()->json($rows);
}

    // Data Adopsi admin (paritas "Admin | Data Adoption" web lama):
    // seluruh baris data_adopsi + atribut pohon & nama donatur.
    // idpohon tidak unik di data_pohon → groupBy data_adopsi.id + MIN()
    // (pola allcertificate). Tagging = pohon sudah di-tag petugas.
    public function adopsilist(){
        $rows=Dataadopsi::leftJoin('member', 'member.id', '=', 'data_adopsi.pengasuh')
            ->leftJoin('data_pohon', 'data_pohon.idpohon', '=', 'data_adopsi.idpohon')
            ->groupBy('data_adopsi.id')
            ->orderByDesc('data_adopsi.id')
            ->selectRaw("data_adopsi.id, data_adopsi.desa, data_adopsi.idpohon, data_adopsi.nama, member.name as donatur, MIN(data_pohon.diameter) as diameter, MIN(data_pohon.localname) as localname, (MIN(data_pohon.adopted) = 'adopted') as tagged, data_adopsi.certnum, data_adopsi.dur, data_adopsi.price, data_adopsi.cur, data_adopsi.methode, data_adopsi.tgl_adopt, data_adopsi.tgl_exp, data_adopsi.invoice, data_adopsi.proses")
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'desa' => $r->desa,
                    'idpohon' => $r->idpohon,
                    'nama' => $r->nama,
                    'donatur' => $r->donatur,
                    'diameter' => $r->diameter !== null ? (int)$r->diameter : null,
                    'localname' => $r->localname,
                    'tagged' => $r->tagged ? 1 : 0,
                    'certnum' => $r->certnum,
                    'dur' => $r->dur !== null ? (int)$r->dur : null,
                    'price' => $r->price !== null ? (int)$r->price : null,
                    'cur' => $r->cur ?: 'IDR',
                    'methode' => $r->methode,
                    'tgl_adopt' => $r->tgl_adopt,
                    'tgl_exp' => $r->tgl_exp,
                    'invoice' => $r->invoice,
                    'proses' => $r->proses,
                ];
            });

        return response()->json($rows);
    }

    // Ringkasan harga pohon per lokasi (paritas "Admin | Data Trees Price"
    // web lama): hitungan pohon per desa × harga × status. Status NULL
    // dibaca 'adopted' (pola mapTree web).
    public function reportprice(){
        $rows=DB::table('data_pohon')
            ->selectRaw("desa, harga, IFNULL(adopted,'adopted') as status, COUNT(*) as jml")
            ->groupByRaw("desa, harga, IFNULL(adopted,'adopted')")
            ->orderBy('desa')
            ->get()
            ->map(function ($r) {
                return [
                    'desa' => $r->desa,
                    'harga' => (int)$r->harga,
                    'status' => $r->status,
                    'jml' => (int)$r->jml,
                ];
            });

        return response()->json($rows);
    }

    // Koreksi baris adopsi (nama penerima, certnum, durasi, donasi, metode,
    // tanggal). Field di-assign satu per satu — fillable Dataadopsi tidak
    // memuat nama & tgl_exp sehingga mass assignment akan senyap membuangnya.
    public function editadopsi(Request $request){
        $data = Dataadopsi::find($request->id);
        if (!$data) {
            return response()->json(['value' => 404, 'pesan' => 'Data adopsi tidak ditemukan']);
        }
        $data->nama = $request->input('nama', $data->nama);
        $data->certnum = $request->input('certnum', $data->certnum);
        $data->dur = $request->input('dur', $data->dur);
        $data->price = $request->input('price', $data->price);
        $data->methode = $request->input('methode', $data->methode);
        $data->tgl_adopt = $request->input('tgl_adopt', $data->tgl_adopt);
        $data->tgl_exp = $request->input('tgl_exp', $data->tgl_exp);
        $data->update();

        return response()->json(['value' => 200, 'pesan' => 'Data adopsi berhasil diperbarui']);
    }

    // Hapus baris adopsi; bila tidak ada baris adopsi lain untuk pohon yang
    // sama, pohon dikembalikan ke available (data_pohon.nama NOT NULL → '').
    public function hapusadopsi(Request $request){
        $data = Dataadopsi::find($request->id);
        if (!$data) {
            return response()->json(['value' => 404, 'pesan' => 'Data adopsi tidak ditemukan']);
        }
        $idpohon = $data->idpohon;
        $data->delete();

        $masihAda = Dataadopsi::where('idpohon', $idpohon)->exists();
        if (!$masihAda) {
            Pohon::where('idpohon', $idpohon)->update([
                'adopted' => 'available',
                'pengasuh' => null,
                'nama' => '',
                'invoice' => null,
                'tgl_adopt' => null,
            ]);
        }

        return response()->json(['value' => 200, 'pesan' => 'Data adopsi berhasil dihapus']);
    }

    // ===================== Backup database =====================
    // Route API ini flat tanpa auth (gaya mobile), sedangkan dump memuat
    // seluruh data termasuk hash password member → SEMUA endpoint backup
    // wajib header X-Backup-Token yang cocok dgn env BACKUP_TOKEN.

    private function backupDir(){
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        return $dir;
    }

    private function cekBackupToken(Request $request){
        $token = env('BACKUP_TOKEN');
        if (!$token) {
            return response()->json(['value' => 503, 'pesan' => 'BACKUP_TOKEN belum diatur di server'], 503);
        }
        if ($request->header('X-Backup-Token') !== $token) {
            return response()->json(['value' => 401, 'pesan' => 'Token backup tidak valid'], 401);
        }
        return null;
    }

    private function namaBackupValid($file){
        return is_string($file) && preg_match('#^pohonasuh2-\d{8}-\d{6}\.sql\.gz$#', $file) === 1;
    }

    public function backuplist(Request $request){
        if ($res = $this->cekBackupToken($request)) return $res;
        $files = glob($this->backupDir().'/*.sql.gz') ?: [];
        usort($files, fn ($a, $b) => strcmp(basename($b), basename($a)));
        $out = array_map(function ($f) {
            return [
                'name' => basename($f),
                'size' => filesize($f),
                'tanggal' => date('Y-m-d H:i:s', filemtime($f)),
            ];
        }, $files);
        return response()->json(['value' => 200, 'files' => $out]);
    }

    // Dump PHP murni (tanpa exec mysqldump agar portabel di hosting):
    // SHOW CREATE TABLE + INSERT batch 100 baris, ditulis streaming ke
    // gzip. NULL jadi literal NULL; nilai lain via PDO::quote.
    public function backupcreate(Request $request){
        if ($res = $this->cekBackupToken($request)) return $res;

        $name = 'pohonasuh2-'.date('Ymd-His').'.sql.gz';
        $path = $this->backupDir().'/'.$name;
        $pdo = DB::connection()->getPdo();
        $gz = gzopen($path, 'wb9');
        if (!$gz) {
            return response()->json(['value' => 500, 'pesan' => 'Gagal menulis file backup'], 500);
        }

        gzwrite($gz, "-- Backup database pohonasuh2\n-- Dihasilkan ".date('Y-m-d H:i:s')."\n\n");
        gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

        $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")
            ->fetchAll(\PDO::FETCH_COLUMN, 0);
        foreach ($tables as $table) {
            $safe = str_replace('`', '``', $table);
            $create = $pdo->query("SHOW CREATE TABLE `{$safe}`")->fetch(\PDO::FETCH_NUM);
            gzwrite($gz, "DROP TABLE IF EXISTS `{$safe}`;\n".$create[1].";\n\n");

            $rows = $pdo->query("SELECT * FROM `{$safe}`");
            $cols = null;
            $batch = [];
            while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
                if ($cols === null) {
                    $cols = '`'.implode('`,`', array_map(fn ($c) => str_replace('`', '``', $c), array_keys($row))).'`';
                }
                $vals = implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row));
                $batch[] = '('.$vals.')';
                if (count($batch) >= 100) {
                    gzwrite($gz, "INSERT INTO `{$safe}` ({$cols}) VALUES\n".implode(",\n", $batch).";\n");
                    $batch = [];
                }
            }
            if ($batch) {
                gzwrite($gz, "INSERT INTO `{$safe}` ({$cols}) VALUES\n".implode(",\n", $batch).";\n");
            }
            gzwrite($gz, "\n");
        }
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);

        return response()->json(['value' => 200, 'file' => $name, 'size' => filesize($path)]);
    }

    public function backupfile(Request $request){
        if ($res = $this->cekBackupToken($request)) return $res;
        $file = (string) $request->query('file', '');
        if (!$this->namaBackupValid($file)) {
            return response()->json(['value' => 400, 'pesan' => 'Nama file tidak valid'], 400);
        }
        $path = $this->backupDir().'/'.$file;
        if (!is_file($path)) {
            return response()->json(['value' => 404, 'pesan' => 'File backup tidak ditemukan'], 404);
        }
        return response()->download($path, $file, ['Content-Type' => 'application/gzip']);
    }

    public function backupdelete(Request $request){
        if ($res = $this->cekBackupToken($request)) return $res;
        $file = (string) $request->input('file', '');
        if (!$this->namaBackupValid($file)) {
            return response()->json(['value' => 400, 'pesan' => 'Nama file tidak valid'], 400);
        }
        $path = $this->backupDir().'/'.$file;
        if (!is_file($path)) {
            return response()->json(['value' => 404, 'pesan' => 'File backup tidak ditemukan'], 404);
        }
        unlink($path);
        return response()->json(['value' => 200, 'pesan' => 'Backup berhasil dihapus']);
    }

  // Pencarian global admin panel: LIKE per entitas, dibatasi 6 baris/grup.
  // LIKE '%q%' tak memakai index → LIMIT wajib agar tetap ringan.
  public function globalsearch(Request $request){
    $q=trim((string)$request->input('q'));
    $kosong=['pohon'=>[],'member'=>[],'sertifikat'=>[],'order'=>[],'blog'=>[],'desa'=>[]];
    if (mb_strlen($q)<2){
      return response()->json($kosong);
    }
    $like='%'.$q.'%';
    $lim=6;

    $pohon=Pohon::where('idpohon','like',$like)
      ->orWhere('localname','like',$like)
      ->orWhere('species','like',$like)
      ->orWhere('desa','like',$like)
      ->orderBy('idpohon')->limit($lim)
      ->get(['id','idpohon','localname','species','desa','adopted']);

    $member=Member::where('name','like',$like)
      ->orWhere('emaile','like',$like)
      ->orWhere('hp','like',$like)
      ->orderByDesc('id')->limit($lim)
      ->get(['id','name','emaile','hp','admin','aktif']);

    // idpohon tidak unik di data_pohon → pola allcertificate:
    // groupBy data_adopsi.id + leftJoin + agregat MIN().
    $sertifikat=Dataadopsi::join('member','member.id','=','data_adopsi.pengasuh')
      ->leftJoin('data_pohon','data_pohon.idpohon','=','data_adopsi.idpohon')
      ->whereNotNull('data_adopsi.certnum')
      ->where('data_adopsi.certnum','!=','')
      ->where(function($w) use ($like){
        $w->where('data_adopsi.certnum','like',$like)
          ->orWhere('data_adopsi.invoice','like',$like);
      })
      ->groupBy('data_adopsi.id')
      ->orderByDesc('data_adopsi.id')
      ->limit($lim)
      ->selectRaw('data_adopsi.id, data_adopsi.certnum, member.name as nama, data_adopsi.idpohon, MIN(data_pohon.localname) as localname, data_adopsi.invoice')
      ->get();

    $order=Confirmasi::where('invoice','like',$like)
      ->orWhere('name','like',$like)
      ->orderByDesc('id')->limit($lim)
      ->get(['id','invoice','name','price','confirmation','tanggal']);

    $blog=Blog::where('name','like',$like)
      ->orWhere('deskripsi','like',$like)
      ->orderByDesc('id')->limit($lim)
      ->get(['id','name','kategori']);

    $desa=Desa::where('nama','like',$like)
      ->orderBy('nama')->limit($lim)
      ->get(['id','nama','provinsi']);

    return response()->json([
      'pohon'=>$pohon,
      'member'=>$member,
      'sertifikat'=>$sertifikat,
      'order'=>$order,
      'blog'=>$blog,
      'desa'=>$desa,
    ]);
  }


  public function updateprosestaging(Request $request){
    $id=$request->id;
    //$idpohon=$request->idpohon;



    $data=Dataadopsi::find($id);
    $idpohon=$data->idpohon;
    $iduser=$data->pengasuh;

    $caritokenmember=Member::where('id',$iduser)->first();
    $tokennotif=$caritokenmember->id_token;
    $data->proses=2;
    $data->update();
    
     $data=new Pesan();
        $data->idmember=$data->pengasuh;
        $data->pesan="Pohon Anda Sedang Dalam Proses Taging";
        $data->status="noread";
        $data->save();
    
    
    /*
 
    $apiURL = 'https://fcm.googleapis.com/fcm/send';
    $body = [
        'to' => $tokennotif,
        'notification' => [
           'body' => "Selamat Pohon anda telah selesai di taging certificat anda dapat di download",
            "title"=> "Certificat Received"
        ],
    ];
    $headers = [
        'Authorization' => 'key=AAAAjVblOiw:APA91bEf3x2qPUYsTXSlyTWoBDQ8h0W_H71un6ro5BvFWhH2zJL6Z0Yrawp3Df8JpHpMbOObZcVewb8WANeda2AqSURsGh3oD3V3U5WhbNYPos708mYGjdOCZfrWzCuLLKbpwzV_YhIM',
        'Content-Type' => 'application/json'
    ];

    $response = Http::withHeaders($headers)->post($apiURL, $body);
    $statusCode = $response->status();
*/



  }
    
    
    
    
    
    
    
    public function feedesa(Request $request){
           $caridesa=Desa::where('id',$request->id)->first();
          if($caridesa){
                $namadesa=$caridesa->nama;
          }else{
                $namadesa="";
          }
          
            // Tanpa join data_pohon: idpohon tidak unik di sana → sum bisa
            // terhitung ganda. desa sudah ada di data_adopsi.
            $jumlahbayar=Dataadopsi::where([['proses',3],['desa',$namadesa]])
            ->sum('price');

        return response()->json([
                'value'=>'200',
                'jumlah' =>$jumlahbayar,
        ]);
                

      
    
    }
    
      
  public function reportdesa(Request $request){
    
    $caridesa=Desa::where('id',$request->id)->first();
    if($caridesa){
        $namadesa=$caridesa->nama;
    }else{
        $namadesa="";
    }
    
    
    
    
    
    
    // idpohon tidak unik di data_pohon → join mem-fan-out baris adopsi.
    // GROUP BY data_adopsi.id + MIN() utk kolom pohon (pola allcertificate).
    $data=Dataadopsi::join('data_pohon','data_adopsi.idpohon','=','data_pohon.idpohon')
      ->where('data_adopsi.desa',$namadesa)
      ->whereIn('data_adopsi.proses',[1,2,3])
      ->groupBy('data_adopsi.id')
      ->orderBy('data_adopsi.proses','desc')
    ->selectRaw('data_adopsi.*, MIN(data_pohon.localname) as localname, MIN(data_pohon.foto_pohon) as foto_pohon, MIN(data_pohon.latitude) as latitude, MIN(data_pohon.longitude) as longitude, MIN(data_pohon.diameter) as diameter, MIN(data_pohon.tinggi) as tinggi, MIN(data_pohon.keliling) as keliling')
    ->get();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['idpohon'] = $k->idpohon;
        $b['pengasuh']=$k->pengasuh;
        $b['nama']=$k->nama;
        $b['price']=$k->price;
        $b['cur']=$k->cur;
        $b['methode']=$k->methode;
        $b['tgl_adopt']=$k->tgl_adopt;
        $b['gfrom']=$k->gfrom;
        $b['certnum']=$k->certnum;
        $b['dur']=$k->dur;
        $b['memo']=$k->memo;
        $b['admin']=$k->admin;
        $b['proses']=$k->proses;
        $b['invoice']=$k->invoice;
       
       
       $cekconfirm=Confirmasi::where('invoice',$k->invoice)->first();
          if($cekconfirm){
        $confirm=$cekconfirm->confirmation;
        
        $ambilnamaconfirmasi=Member::where('id',$cekconfirm->confirmationBy)->first();
        if($ambilnamaconfirmasi){
        $namanya=$ambilnamaconfirmasi->name;    
        }else{
            $namanya="";
        }
        
        
        
        $confirmBy=$cekconfirm->confirmationBy;
        
        }else{
            $confirm="";    
        }
        
        $idconfirmasi=Confirmasi::where('invoice',$k->invoice)->first();
          if($idconfirmasi){
        $confirmid=$idconfirmasi->id;    
        }else{
            $confirmid="";    
        }
        $b['confirmasiBy']=$namanya;        
        $b['confirmasi']=$confirm;
        $b['localname']=$k->localname;
        $b['created_at']=$k->created_at->format('d-M-Y h:m:s');
        $b['foto_pohon']=$k->foto_pohon;
        $b['latitude']=$k->latitude;
        $b['longitude']=$k->longitude;
        $b['diameter']=$k->diameter;
        $b['tinggi']=$k->tinggi;
        $b['keliling']=$k->keliling;
        $b['tgl_exp']=$k->tgl_exp;
        
        
        $carifototransaksi=Confirmasi::where('invoice',$k->invoice)->first();
        
        if($carifototransaksi){
        $foto=$carifototransaksi->foto;    
        }else{
            $foto="";    
        }
        
         $cariid=Confirmasi::where('invoice',$k->invoice)->first();
        
        if($cariid){
        $idnya=$cariid->id;    
        }else{
            $idnya="";    
        }
        
        $b['foto']=$foto;
        $b['idconfirmasi']=$idnya;
        
         $cekfoto=Confirmasi::where('invoice',$k->invoice)->first();
          if($cekfoto){
        $fotopembayaran=$cekfoto->foto;    
        }else{
            $fotopembayaran="";    
        }
         
         
        // dd($cekfoto);
       
          $b['fotopembayaran']=$fotopembayaran;
          $b['desa']=$k->desa;
          $b['confirmasiid']=$confirmid;
        

        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }

  }
    
     
  public function getnoted(Request $request){
   $data=Detailimage::where('idorder',$request->idorder)->first();
    if($data){
    
      return response()->json(
            [
              'message'=> $data->note,
              'value'=>200 
            ]
            );
     
    }else{
        
      return response()->json(
            [
              'message'=> '-',
              'value'=>200 
            ]
            );
 
    }
    

  }
    
  public function updatenoted(Request $request){
   $data=Detailimage::where('idorder',$request->idorder)->get();
   if(count($data)>0){
    foreach ($data as $k) {
      $do=Detailimage::find($k->id);
      $do->note=$request->note;
      $do->update();

    }
      return response()->json(
            [
              'message'=> 'Update Note Sukses',
              'value'=>200
            ]
            );
  }else {
    // Belum ada baris foto utk order ini → buat baris catatan saja
    // (urlnya null, tidak tampil sebagai foto di getdetailimage).
    $baru=new Detailimage();
    $baru->idorder=$request->idorder;
    $baru->note=$request->note;
    $baru->save();
      return response()->json(
            [
              'message'=> 'Update Note Sukses',
              'value'=>200
            ]
            );
  }

  }
    
    
    
     public function setPohonremove(Request $request){
         $data=Pohon::find($request->id);
         
        $data->highlight=null;
        $data->update();
          return response()->json(
            [
              'message'=> 'Update Pohon Sukses',
              'value'=>200 
            ]
            );
    }
    
    public function setPohonhighlight(Request $request){
         $data=Pohon::find($request->id);
        $data->highlight=2;
        $data->update();
          return response()->json(
            [
              'message'=> 'Update Pohon Sukses',
              'value'=>200 
            ]
            );
    }
    
    public function setPohonterbaik(Request $request){
        $data=Pohon::find($request->id);
        $data->highlight=1;
        $data->update();
          return response()->json(
            [
              'message'=> 'Update Pohon Terbaik Sukses',
              'value'=>200 
            ]
            );
        
        
    }
 
  public function mypesanupdate(Request $request){
        $data=Pesan::find($request->id);
        $data->status="read";
        $data->update();
      
  } 
 
  public function mypesandelete(Request $request){
        $data=Pesan::where('id',$request->id);
        if($data){
          $data->delete();
          return response()->json(
            [
              'message'=> 'Delete Success',
              'code'=>200 
            ]
            );
        }else{
          return response()->json(
            [
              'message'=> "Data dengan ID = $id Tidak Ditemukan",
              'code'=>404
            ]
            );
        }
      
  }    
    
 
  public function listPesanku(Request $request){
      $cek=Pesan::where('idmember',$request->idmember)->get();
        if(count($cek)>0){
                    $items = array();
            foreach($cek as $k){
            $b['id']=$k->id;
            $b['pesan']=$k->pesan;
            $b['status']=$k->status;
            $b['tanggal']=$k->created_at ? $k->created_at->format('d M Y, H:i') : null;
            array_push($items, $b);
            }
          return response()->json($items);
      }else {
        $items = array();
        array_push($items);
        return response()->json($items);
      }
      
  }    
    
  public function getPesanku(Request $request){
      $cek=Pesan::where([['idmember',$request->idmember],['status','noread']])->get();
      $jumlahpesan=0;
      if($cek){
          $data=Pesan::where([['idmember',$request->idmember],['status','noread']])->count();
            $jumlahpesan=$data;  
        return response()->json([
                'value'=>'200',
                'jumlah' =>$jumlahpesan,
        ]);
      }else{
          return response()->json([
                'value'=>'205',
                'jumlah' =>0,
        ]);
      }
      
  }
    
    public function pesanNotif(Request $request){
        $data=new Pesan();
        $data->idmember=$request->input("idmember");
        $data->pesan=$request->input("pesan");
        $data->status="noread";
        $data->save();
    }
    
    public function hapustransaksitidakjadi(Request $request){
       
        $tgl_kemarin=date('Y-m-d', strtotime("-1 day", strtotime(date("Y-m-d"))));
      //  $datanya=Confirmasi::where([['confirmation','no'],['tgl_pesan',$tgl_kemarin]])->get();
        $datanya=Confirmasi::where('id',$request->id)->get();
     if(count($datanya)>0){
         foreach ($datanya as $k) {
             
            $hapusdataadopsi=Dataadopsi::where('invoice',$k->invoice)->get();
            
            if(count($hapusdataadopsi)>0){
                foreach($hapusdataadopsi as $j){
                   $balikinjadiaviable=Pohon::where('idpohon',$j->idpohon)->get();
                    if(count($balikinjadiaviable)>0){
                            foreach($balikinjadiaviable as $p){
                                $av=Pohon::find($p->id);
                                $av->adopted="available";
                                $av->update();
                                
                            }
                    }   
                       
                    $datat=Dataadopsi::find($j->id);
                   $datat->delete();
                       
                   
                }
                 return response()->json([
                'value'=>'200',
                
        ]);
                
            }
             
    $data=Confirmasi::find($k->id);
    $data->delete();
           return response()->json([
                'value'=>'200',
                
        ]);
         }
     }
    }
    
    
    public function mytreesgroup(Request $request){
      $iduser=$request->iduser;
      $data=Dataadopsi::join('data_pohon','data_adopsi.idpohon','=','data_pohon.idpohon')
     // ->join('desa','data_adopsi.desa','=','desa.nama')
      ->where('data_adopsi.pengasuh',$iduser)
      ->groupBy(['data_adopsi.*','data_pohon.localname','data_pohon.foto_pohon'])
      ->get(['data_adopsi.*','data_pohon.localname','data_pohon.foto_pohon']);
      if(count($data)>0){
        $items = array();
        foreach ($data as $k) {
          $b['id'] = $k->id;
          $b['idpohon'] = $k->idpohon;
          $b['pengasuh']=$k->pengasuh;
          $b['nama']=$k->nama;
          $b['price']=$k->price;
          $b['cur']=$k->cur;
          $b['methode']=$k->methode;
          $b['tgl_adopt']=$k->tgl_adopt;
          $b['gfrom']=$k->gfrom;
          $b['certnum']=$k->certnum;
          $b['dur']=$k->dur;
          $b['memo']=$k->memo;
          $b['admin']=$k->admin;
          $b['proses']=$k->proses;
          $b['invoice']=$k->invoice;
          $b['localname']=$k->localname;
          $b['created_at']=$k->created_at->format('d-M-Y h:m:s');
          $b['foto_pohon']=$k->foto_pohon;
          $b['desa']=$k->desa;
          $b['tgl_exp']=$k->tgl_exp;
          $cekconfirm=Confirmasi::where('invoice',$k->invoice)->first();
          if($cekconfirm){
              $a=$cekconfirm->confirmation;
          }else{
              $a="";
          }
       
          $b['confirm']=$a;
          
          
          array_push($items, $b);
        }
        return response()->json($items);
      }else {
        $items = array();
        array_push($items);
        return response()->json($items);
      }




    }
    
    
    


  public function mycertificate(Request $request){
      
      $data=Confirmasi::where('idpengasuh',$request->idpengasuh)
      ->orderBy('tanggal','desc')
       ->get();
   
        if(count($data)>0){
                 $items = array();
                 // preload info desa (kecamatan/kabupaten/provinsi) untuk
                 // hindari query per baris
                 $namaDesa = Dataadopsi::whereIn('invoice', $data->pluck('invoice'))
                    ->pluck('desa')->unique()->filter();
                 $infoDesa = Desa::whereIn('nama', $namaDesa)->get()->keyBy('nama');
                 foreach ($data as $k) {
                 $b['id'] = $k->id;
                 $b['invoice'] = $k->invoice;
                 $b['price'] = $k->price;
                 $b['tanggal'] = $k->tanggal;
                 $b['jml_pohon'] = $k->jml_pohon;
                 $b['foto'] = $k->foto;
                 $b['confirmation'] = $k->confirmation;
                $da=Dataadopsi::where('data_adopsi.invoice',$k->invoice)
                ->first();
                if($da){
                    $b['tgl_exp'] = $da->tgl_exp;
                    $b['desa'] = $da->desa;
                    $b['memo'] = $da->memo;
                    $b['tgl_adopt'] = $da->tgl_adopt;
                    $b['nama'] = $da->nama;
                    $b['certnum'] = $da->certnum;
                    $dv = $infoDesa->get($da->desa);
                    $b['kecamatan'] = $dv->kecamatan ?? '-';
                    $b['kabupaten'] = $dv->kabupaten ?? '-';
                    $b['provinsi'] = $dv->provinsi ?? '-';

                }else{
                    $b['tgl_exp'] = "-";
                }
     
              
                 
                 
                 array_push($items, $b);
        }
        return response()->json($items);
        }else {
            $items = array();
            array_push($items);
            return response()->json($items);
        }
  }








public function updateduration(Request $request){
    
    $data=Tobasket::where('id_member',$request->input('id_member'))->get();
     if(count($data)>0){
                 $items = array();
                 foreach ($data as $k) {
                 $carihargapohon=Pohon::where('idpohon',$k->id_pohon)->first();
                 if($carihargapohon){
                     $hargapohon=$carihargapohon->harga;
                 }else{
                     $hargapohon=0;
                 }
                     
                 $b['id'] = $k->id;
                 $b['y']=$k->y;
                 $ab=Tobasket::where('id',$k->id)->first();
                 
                 $ab->y=$request->input('duration');
                
                 
                 $harga=$hargapohon*$request->input('duration');
                 $ab->subtotal=$harga;
                 $ab->update();
                 $b['pesan']="Berhasil Update";
                // $b['harganya']=$hargapohon;
                
                 
                 array_push($items, $b);
        }
        return response()->json($items);
        }else {
            $items = array();
            array_push($items);
            return response()->json($items);
        }
}

  public function testjson(Request $request){

    $data = request()->all();
    foreach ($data as $key => $value) {
        $simpan=new Testjson();
        $simpan->name=$value['name'];
        $simpan->qty=$value['qty'];
        $simpan->save();
    }
     
  }
  
  public function updatememo(Request $request){
      $id=$request->input('id');
      $invoice=$request->input('invoice');
      $ab=Dataadopsi::where('invoice',$invoice)->get();
      if(count($ab)>0){
         foreach ($ab as $k) {
    
      $data=Dataadopsi::find($k->id);
      $data->memo=$request->input('memo');
      $data->nama=$request->input('nama');
      $data->update();
         }
      }
        return response()->json(
                        [
                          'message'=> "Succes update",
                          'code'=>200 
                        ]
                     );
     
   
                     
                     
      
  }
  
  // Detail satu invoice untuk halaman koreksi nama/memo admin web:
  // nama & memo per invoice dipakai di sertifikat (sertifikatpublik).
  public function orderbyinvoice(Request $request){
      $invoice=$request->input('invoice');
      $rows=Dataadopsi::join('data_pohon','data_pohon.idpohon','=','data_adopsi.idpohon')
        ->where('data_adopsi.invoice',$invoice)
        ->groupBy('data_adopsi.id')
        ->orderBy('data_adopsi.id')
        ->selectRaw('data_adopsi.id, data_adopsi.invoice, data_adopsi.idpohon, data_adopsi.nama, data_adopsi.memo, data_adopsi.pengasuh, data_adopsi.proses, data_adopsi.price, data_adopsi.tgl_adopt, data_adopsi.tgl_exp, MIN(data_pohon.localname) as localname, MIN(data_pohon.desa) as desa')
        ->get()
        ->map(function($r){
            $carimember=Member::where('id',$r->pengasuh)->first();
            return [
                'id'=>$r->id,
                'invoice'=>$r->invoice,
                'idpohon'=>$r->idpohon,
                'localname'=>$r->localname ?? '-',
                'desa'=>$r->desa ?? '-',
                'nama'=>$r->nama,
                'nama_member'=>$carimember ? $carimember->name : '-',
                'memo'=>$r->memo,
                'proses'=>$r->proses,
                'price'=>$r->price,
                'tgl_adopt'=>$r->tgl_adopt,
                'tgl_exp'=>$r->tgl_exp,
            ];
        });
      return response()->json($rows);
  }

  public function verivication(Request $request){
       $data=Confirmasi::find($request->id);
                 $data->confirmation="yes";
                 $data->confirmationBy=$request->iduser;
                 $data->update();
                 //memberikan informasi bila status pembayaranya sudah di verifikasi
        $cariidpengasuh=Confirmasi::where('id',$request->id)->first();
                 
                 
                   $data=new Pesan();
        $data->idmember=$cariidpengasuh->idpengasuh;
        $data->pesan="Congratulations, your payment has been verified";
        $data->status="noread";
        $data->save();
        $this->kirimPush($cariidpengasuh->idpengasuh, 'Payment Verified', 'Congratulations, your payment has been verified');

        // terbitkan nomor sertifikat untuk order yang baru diverifikasi
        $this->isiCertnum($cariidpengasuh->invoice);

                     return response()->json(
                        [
                          'message'=> "Succes Verivication",
                          'code'=>200
                        ]
                     );
  }

  // Nomor sertifikat: {urut 3 digit}/LPHD-{inisial desa}/{tahun} — mengikuti
  // pola data lama (KPHD-RK/2019 dst.) dan contoh template baru
  // (LPHD-MT/2026). Urut dihitung per kode desa+tahun dari certnum yang
  // sudah ada.
  private function certnumBaru($desa){
    // kode cert eksplisit dari kelola lokasi (bila diisi) menang atas derivasi
    $kodes = trim((string)Desa::where('nama', $desa)->value('kode_cert'));
    if ($kodes !== '') {
      $kode = strtoupper($kodes);
      $tahun = date('Y');
      $ada = Dataadopsi::where('certnum', 'like', '%/LPHD-'.$kode.'/'.$tahun)
        ->distinct()->count('certnum');
      return sprintf('%03d/LPHD-%s/%s', $ada + 1, $kode, $tahun);
    }
    $kata = preg_split('/\s+/', trim((string)$desa));
    $kode = '';
    foreach ($kata as $k) { $kode .= mb_substr($k, 0, 1); }
    if (mb_strlen($kode) < 2) {
      // nama satu kata (mis. "rantaukermas") → 2 huruf pertama
      $kode = mb_substr(trim((string)$desa), 0, 2);
    }
    $kode = strtoupper($kode !== '' ? $kode : 'XX');
    $tahun = date('Y');
    $ada = Dataadopsi::where('certnum', 'like', '%/LPHD-'.$kode.'/'.$tahun)
      ->distinct()->count('certnum');
    return sprintf('%03d/LPHD-%s/%s', $ada + 1, $kode, $tahun);
  }

  // Isi certnum semua baris data_adopsi satu invoice (sekali saja; order
  // satu sertifikat walaupun berisi beberapa pohon).
  private function isiCertnum($invoice){
    $rows = Dataadopsi::where('invoice', $invoice)->get();
    if ($rows->isEmpty()) return;
    if (trim((string)$rows->first()->certnum) !== '') return;
    $certnum = $this->certnumBaru($rows->first()->desa);
    foreach ($rows as $r) {
      $r->certnum = $certnum;
      $r->save();
    }
  }


  // Membatalkan verifikasi pembayaran: order dibatalkan, semua pohon pada
  // invoice dikembalikan ke "available" agar bisa diadopsi user lain, dan
  // data adopsi + foto taging terkait dihapus.
  public function batalverivication(Request $request){
       $data=Confirmasi::find($request->id);
       if(!$data){
                  return response()->json(
                        [
                          'message'=> "Confirmation Not Found",
                          'code'=>404
                        ]
                     );
       }
       $data->confirmation="cancel";
       $data->confirmationBy=$request->iduser;
       $data->update();

       $adopsi=Dataadopsi::where('invoice',$data->invoice)->get();
       foreach ($adopsi as $a) {
                  Fototaging::where('idadopsi',$a->id)->delete();
                  $pohon=Pohon::where('idpohon',$a->idpohon)->first();
                  if($pohon){
                            $pohon->adopted="available";
                            // bersihkan jejak adopsi batal agar pohon betul-betul
                            // "bersih" untuk diadopsi donor lain (pola hapusadopsi)
                            $pohon->pengasuh=null;
                            $pohon->nama='';
                            $pohon->invoice=null;
                            $pohon->tgl_adopt=null;
                            $pohon->update();
                  }
       }
       Dataadopsi::where('invoice',$data->invoice)->delete();

                 //memberikan informasi bila order dibatalkan
                 $pesan=new Pesan();
       $pesan->idmember=$data->idpengasuh;
       $pesan->pesan="Sorry, your order has been canceled";
       $pesan->status="noread";
       $pesan->save();
       $this->kirimPush($data->idpengasuh, 'Order Canceled', 'Sorry, your order has been canceled');

                  return response()->json(
                        [
                          'message'=> "Succes Cancel Verivication",
                          'code'=>200
                        ]
                     );
  }


  // ===================== Mayar payment gateway =====================
  // Buat invoice Mayar untuk sebuah confirmation (jalur "Bayar Online").
  // Idempoten: bila invoice sudah pernah dibuat, balas link lama —
  // sekaligus menangkal 429 duplicate-request Mayar (jendela 1 menit).
  public function createinvoice(Request $request){
    $apikey = trim((string)env('MAYAR_API_KEY'));
    if ($apikey === '') {
      return response()->json([
        'code' => 500,
        'message' => 'MAYAR_API_KEY belum dikonfigurasi di server',
      ]);
    }

    $conf = null;
    if ($request->filled('id')) {
      $conf = Confirmasi::find($request->id);
    } elseif ($request->filled('idmember')) {
      $conf = Confirmasi::where('idpengasuh', $request->idmember)->orderByDesc('id')->first();
    }
    if (!$conf) {
      return response()->json([
        'code' => 404,
        'message' => 'Confirmation Not Found',
      ]);
    }

    if ($conf->link_invoice) {
      return response()->json([
        'code' => 200,
        'id' => $conf->id,
        'link' => $conf->link_invoice,
        'idinvoice' => $conf->idinvoice_mayar,
        'message' => 'Invoice sudah pernah dibuat',
      ]);
    }

    // Item invoice dari pohon pada order: rate = harga per tahun, quantity = durasi (year).
    $rows = Dataadopsi::where('invoice', $conf->invoice)->get();
    if (count($rows) === 0) {
      return response()->json([
        'code' => 404,
        'message' => 'Order untuk invoice ini kosong',
      ]);
    }

    $items = [];
    $subtotal = 0;
    foreach ($rows as $r) {
      $pohon = Pohon::where('idpohon', $r->idpohon)->first();
      $dur = max(1, (int)$r->dur);
      $rate = (int)round(((int)$r->price) / $dur);
      $items[] = [
        'quantity' => $dur,
        'rate' => $rate,
        'description' => 'Adopsi pohon ' . ($pohon ? $pohon->localname : $r->idpohon) . ' (' . $r->idpohon . ')',
      ];
      $subtotal += $rate * $dur;
    }
    // confirmation.price dapat memuat kode unik transfer → item penyesuaian.
    $selisih = ((int)$conf->price) - $subtotal;
    if ($selisih > 0) {
      $items[] = [
        'quantity' => 1,
        'rate' => $selisih,
        'description' => 'Kode unik transfer',
      ];
    }

    $member = Member::find($conf->idpengasuh);

    $ttl = max(1, (int)env('MAYAR_INVOICE_TTL_HOURS', 24));
    $expiredAt = gmdate('Y-m-d\TH:i:s\Z', time() + $ttl * 3600);

    try {
      $resp = Http::withToken($apikey)
        ->timeout(30)
        ->post(rtrim((string)env('MAYAR_BASE_URL', 'https://api.mayar.id/hl/v2'), '/') . '/invoices/create', [
          'name' => $conf->name,
          'email' => $conf->email,
          'mobile' => $this->mayarMobile($member ? $member->hp : null),
          'description' => 'Pohon Asuh - invoice ' . $conf->invoice,
          'expiredAt' => $expiredAt,
          'items' => $items,
          'extraData' => [
            // Mayar mensyaratkan nilai extraData berupa string
            'idconfirmation' => (string)$conf->id,
            'invoice' => (string)$conf->invoice,
          ],
        ]);
    } catch (\Throwable $e) {
      Log::error('createinvoice: gagal terhubung ke Mayar', ['error' => $e->getMessage()]);
      return response()->json([
        'code' => 502,
        'message' => 'Gagal terhubung ke Mayar: ' . $e->getMessage(),
      ]);
    }

    if (!$resp->successful()) {
      Log::error('createinvoice: Mayar menolak request', [
        'status' => $resp->status(),
        'body' => $resp->body(),
      ]);
      return response()->json([
        'code' => $resp->status(),
        'message' => 'Mayar menolak pembuatan invoice',
        'detail' => $resp->json(),
      ]);
    }

    $data = $resp->json('data');
    $link = $data['link'] ?? null;
    if (!$link) {
      Log::error('createinvoice: respons Mayar tanpa link', ['body' => $resp->body()]);
      return response()->json([
        'code' => 502,
        'message' => 'Respons Mayar tidak memuat link invoice',
      ]);
    }

    $conf->idinvoice_mayar = $data['id'] ?? null;
    $conf->link_invoice = $link;
    $conf->update();

    return response()->json([
      'code' => 200,
      'id' => $conf->id,
      'link' => $link,
      'idinvoice' => $conf->idinvoice_mayar,
      'message' => 'Invoice created',
    ]);
  }

  // Webhook Mayar. payment.received = pembayaran LUNAS → verifikasi
  // otomatis confirmation (paritas perilaku verivication manual).
  public function webhookmayar(Request $request){
    $payload = $request->all();
    Log::info('webhook mayar', $payload);

    $event = $payload['event'] ?? '';
    if ($event !== 'payment.received') {
      return response()->json(['ok' => true, 'ignored' => $event]);
    }

    $d = $payload['data'] ?? [];
    if (!is_array($d)) {
      return response()->json(['ok' => false, 'message' => 'payload tidak valid']);
    }

    // extraData dapat hadir sebagai objek, string JSON, atau lewat custom_field.
    $extra = $d['extraData'] ?? null;
    if (is_string($extra)) {
      $extra = json_decode($extra, true);
    }
    if (!is_array($extra)) {
      $cf = $d['custom_field'] ?? null;
      if (is_string($cf)) {
        $cf = json_decode($cf, true);
      }
      $extra = is_array($cf) ? $cf : [];
    }

    $conf = null;
    if (!empty($extra['idconfirmation'])) {
      $conf = Confirmasi::find($extra['idconfirmation']);
    }
    if (!$conf && !empty($d['id'])) {
      $conf = Confirmasi::where('idinvoice_mayar', $d['id'])->first();
    }
    if (!$conf && !empty($d['amount']) && !empty($d['customerEmail'])) {
      $conf = Confirmasi::where('price', (int)$d['amount'])
        ->where('email', $d['customerEmail'])
        ->orderByDesc('id')
        ->first();
    }
    if (!$conf) {
      Log::warning('webhook mayar: confirmation tidak ditemukan', $payload);
      return response()->json(['ok' => false, 'message' => 'confirmation tidak ditemukan']);
    }

    if ($conf->confirmation === 'yes') {
      return response()->json(['ok' => true, 'message' => 'sudah diverifikasi', 'id' => $conf->id]);
    }

    $conf->confirmation = 'yes';
    $conf->confirmationBy = 'mayar';
    $conf->update();

    // informasi ke customer — pola verivication()
    $pesan = new Pesan();
    $pesan->idmember = $conf->idpengasuh;
    $pesan->pesan = 'Congratulations, your payment has been verified';
    $pesan->status = 'noread';
    $pesan->save();
    $this->kirimPush($conf->idpengasuh, 'Payment Verified', 'Congratulations, your payment has been verified');

    // terbitkan nomor sertifikat (paritas verivication manual)
    $this->isiCertnum($conf->invoice);

    return response()->json([
      'ok' => true,
      'code' => 200,
      'id' => $conf->id,
      'message' => 'Verifikasi otomatis berhasil',
    ]);
  }

  // Normalisasi no HP untuk Mayar: buang spasi/tanda, +62/62 → 08…
  // Kosong ATAU hasil buang-tanda tak sampai 10 digit (Mayar wajib ≥10,
  // mis. hp berisi "usercoba") → nomor default .env.
  private function mayarMobile($hp){
    $b = preg_replace('/[^0-9+]/', '', trim((string)$hp));
    if ($b === '') {
      $b = (string)env('MAYAR_DEFAULT_MOBILE', '085709947075');
    }
    if (str_starts_with($b, '+62')) {
      $b = '0' . substr($b, 3);
    } elseif (str_starts_with($b, '62') && strlen($b) > 10) {
      $b = '0' . substr($b, 2);
    }
    if (strlen($b) < 10) {
      return (string)env('MAYAR_DEFAULT_MOBILE', '085709947075');
    }
    return $b;
  }


public function getconfirmasi(Request $request){
      
      
      $data=Confirmasi::where('idpengasuh',$request->idmember)
      ->orderBy('tanggal','desc')
       ->get();
   
        if(count($data)>0){
                 $items = array();
                 foreach ($data as $k) {
                 $b['id'] = $k->id;
                 $b['invoice'] = $k->invoice;
                 $b['price'] = $k->price;
                 $b['tanggal'] = $k->tanggal;
                 $b['jml_pohon'] = $k->jml_pohon;
                 $b['confirmation'] = $k->confirmation;
                 $b['foto'] = !empty($k->foto)
                     ? $request->getSchemeAndHttpHost().str_replace('/index.php', '', $request->getBaseUrl()).'/upload/slider/'.$k->foto
                     : null;
                 $b['link_invoice'] = $k->link_invoice;
                 
                 array_push($items, $b);
        }
        return response()->json($items);
        }else {
            $items = array();
            array_push($items);
            return response()->json($items);
        }
  }
  
  
  
  public function uploadbuktitransfer(Request $request){
       if ($request->hasFile('image')) {
                 $file = $request->file('image');
                 $name_file = time().'.'.$file->getClientOriginalExtension();
                 $extension = $file->getClientOriginalExtension();
                 $ukuran_file = $file->getSize();
                 $destinationPath = base_path('public/upload/slider');
                 if (!file_exists($destinationPath)) {
                     mkdir($destinationPath, 0775, true);
                 }
                 $file->move($destinationPath,$name_file);
                 $data=Confirmasi::find($request->id);
                 $data->foto=       $name_file;
                 $data->update();
                     return response()->json(
                        [
                          'message'=> "Succes Upload",
                          'code'=>200 
                        ]
                     );
                 
       }
       
       
       
  }


public function confirmasipembayaran(Request $request){

    
    //menambahkan kedatabase konfirmasi pembayaran;
    if ($request->hasFile('image')) {
                 $file = $request->file('image');
                 $name_file = time().'.'.$file->getClientOriginalExtension();
                 $extension = $file->getClientOriginalExtension();
                 $ukuran_file = $file->getSize();
                 $destinationPath = 'public/upload/slider';
                 $file->move($destinationPath,$name_file);
                  //membuat nomer invoce otomatis
                    $tanggal=Date('Ymd');
                    $tanggalpesan=Date('Y-m-d');
                    // urut hanya dari invoice numerik "#Ymd<urut>" — invoice
                    // non-numerik (fixture/demo manual) tidak boleh meracuni
                    // penomoran (TypeError string + int di PHP 8)
                    $ceknomertransaksi=Confirmasi::where('invoice','like','#%')
                        ->whereRaw("invoice REGEXP '^#[0-9]{8}[0-9]+$'")
                        ->orderByDesc('invoice')
                        ->first();
                    $nomerselanjutnya=$ceknomertransaksi ? (int)substr($ceknomertransaksi->invoice,9) : 0;
                    $simbol="#";
                    $nomerinvoice=$simbol.$tanggal.($nomerselanjutnya+1);
                    
                    
                    $data=new Confirmasi();
                    $data->invoice=$nomerinvoice;
                    $data->tgl_pesan=$tanggalpesan;
                    $data->idpengasuh=$request->input('id_member');
                    $data->name=$request->input('name');
                    $data->email=$request->input('email');
                    $data->methode=$request->input('methode');
                    $data->cur="IDR";
                    $data->price=$request->input('price');
                    $data->tanggal=$tanggalpesan;
                    $data->jml_pohon=$request->input('jml_pohon');
                    $data->confirmation="no";
                    $data->foto=$name_file;
                    $data->save();
                    $idconfirmation=$data->id;
                  //mencari detail pohon yang di beli oleh member;
        $bd=Tobasket::where('id_member',$request->id_member)->get();
        if(count($bd)>0){
        
         foreach ($bd as $k) {
            $caridatapohon=Pohon::where('idpohon',$k->id_pohon)->first();
            $carihargaperpohon=Tobasket::where([['id_pohon',$k->id_pohon],['id_member',$request->input('id_member')]])->first();
            //update status pohon 
               
    $caridatapohon->adopted="reserved";
    $caridatapohon->update();
    
            //
            $t=$carihargaperpohon->y;
              //data untuk menyimpan ke data adopsi
            
            $idpohon=$k->id_pohon;
            $desa=$caridatapohon->desa;
            $pengasuh=$request->input('id_member');
            // nama penerima (data_adopsi.nama — tercetak di sertifikat):
            // pakai nama penerima hadiah dari basket bila diisi, selain itu
            // nama pemesan.
            $nama=trim((string)($k->nama ?? ''));
            if($nama===''){ $nama=$request->input('name'); }
            $price=$carihargaperpohon->subtotal;
            $cur="IDR";
            $methode=$request->input('methode');
            $tgl_adopt=$carihargaperpohon->tanggal;
            $certnum="";
            $dur=$carihargaperpohon->y;
            $memo=$carihargaperpohon->pesan ?? '';
            $admin=0;
            $proses=1;
            $invoice=$nomerinvoice;
            $tgl_exp = date('Y-m-d', strtotime(''.+$t.' year',  strtotime($carihargaperpohon->tanggal)));
           
            
                   
            $data=new Dataadopsi();
            $data->idpohon=$idpohon;
            $data->desa=$desa;
            $data->pengasuh=$pengasuh;
            $data->nama=$nama;
            $data->price=$carihargaperpohon->subtotal;
            $data->cur="IDR";
            $data->methode="Transfer";
            $data->tgl_adopt=$tgl_adopt;
            $data->gfrom=0;
            $data->certnum="";
            $data->memo=$memo;
            $data->admin=0;
            $data->proses=1;
            $data->invoice=$invoice;
            $data->dur=$carihargaperpohon->y;
            $data->tgl_exp=$tgl_exp;
            $data->save(); 
            
         }
         $hapuskeranjang=Tobasket::where('id_member',$request->id_member);
         if($hapuskeranjang){
             $hapuskeranjang->delete();
         }else{

         }

         // beri tahu admin soal order baru (pesan in-app + push FCM)
         $this->notifikasiCheckoutAdmin($request->input('id_member'));
      
          return response()->json(
            [
              'message'=> "Succes Add",
              'code'=>200,
              'id'=> $idconfirmation
            ]
       );
                 
                 
    }
    
    //akhhir 
    
    
    
    
    
    
    
    
    
    
    
   
         
    }else{ //membuat nomer invoce otomatis
                    $tanggal=Date('Ymd');
                    $tanggalpesan=Date('Y-m-d');
                    // urut hanya dari invoice numerik "#Ymd<urut>" — invoice
                    // non-numerik (fixture/demo manual) tidak boleh meracuni
                    // penomoran (TypeError string + int di PHP 8)
                    $ceknomertransaksi=Confirmasi::where('invoice','like','#%')
                        ->whereRaw("invoice REGEXP '^#[0-9]{8}[0-9]+$'")
                        ->orderByDesc('invoice')
                        ->first();
                    $nomerselanjutnya=$ceknomertransaksi ? (int)substr($ceknomertransaksi->invoice,9) : 0;
                    $simbol="#";
                    $nomerinvoice=$simbol.$tanggal.($nomerselanjutnya+1);
                    
                    
                    $data=new Confirmasi();
                    $data->invoice=$nomerinvoice;
                    $data->tgl_pesan=$tanggalpesan;
                    $data->idpengasuh=$request->input('id_member');
                    $data->name=$request->input('name');
                    $data->email=$request->input('email');
                    $data->methode=$request->input('methode');
                    $data->cur="IDR";
                    $data->price=$request->input('price');
                    $data->tanggal=$tanggalpesan;
                    $data->jml_pohon=$request->input('jml_pohon');
                    $data->confirmation="no";

                    $data->save();
                    $idconfirmation=$data->id;
                  //mencari detail pohon yang di beli oleh member;
    $bd=Tobasket::where('id_member',$request->id_member)->get();
    if(count($bd)>0){
        
         foreach ($bd as $k) {
            $caridatapohon=Pohon::where('idpohon',$k->id_pohon)->first();
            $carihargaperpohon=Tobasket::where([['id_pohon',$k->id_pohon],['id_member',$request->input('id_member')]])->first();
                         
            $caridatapohon->adopted="reserved";
            $caridatapohon->update();
            $t=$carihargaperpohon->y;
              //data untuk menyimpan ke data adopsi
            
            $idpohon=$k->id_pohon;
            $desa=$caridatapohon->desa;
            $pengasuh=$request->input('id_member');
            // nama penerima (data_adopsi.nama — tercetak di sertifikat):
            // pakai nama penerima hadiah dari basket bila diisi, selain itu
            // nama pemesan.
            $nama=trim((string)($k->nama ?? ''));
            if($nama===''){ $nama=$request->input('name'); }
            $price=$carihargaperpohon->subtotal;
            $cur="IDR";
            $methode=$request->input('methode');
            $tgl_adopt=$carihargaperpohon->tanggal;
            $certnum="";
            $dur=$carihargaperpohon->y;
            $memo=$carihargaperpohon->pesan ?? '';
            $admin=0;
            $proses=1;
            $invoice=$nomerinvoice;
            $tgl_exp = date('Y-m-d', strtotime(''.+$t.' year',  strtotime($carihargaperpohon->tanggal)));
           
            
                   
            $data=new Dataadopsi();
            $data->idpohon=$idpohon;
            $data->desa=$desa;
            $data->pengasuh=$pengasuh;
            $data->nama=$nama;
            $data->price=$carihargaperpohon->subtotal;
            $data->cur="IDR";
            $data->methode="Transfer";
            $data->tgl_adopt=$tgl_adopt;
            $data->gfrom=0;
            $data->certnum="";
            $data->memo=$memo;
            $data->admin=0;
            $data->proses=1;
            $data->invoice=$invoice;
            $data->dur=$carihargaperpohon->y;
            $data->tgl_exp=$tgl_exp;
            $data->save(); 
            
         }
         $hapuskeranjang=Tobasket::where('id_member',$request->id_member);
         if($hapuskeranjang){
             $hapuskeranjang->delete();
         }else{

         }

         // beri tahu admin soal order baru (pesan in-app + push FCM)
         $this->notifikasiCheckoutAdmin($request->input('id_member'));
      
          return response()->json(
            [
              'message'=> "Succes Add",
              'code'=>200,
              'id'=> $idconfirmation
            ]
       );
                 
                 
    }
    }
    
    }
  
  
  public function mytrolleydelete(Request $request){
        $data=Tobasket::where('id',$request->id);
        if($data){
          $data->delete();
          return response()->json(
            [
              'message'=> 'Delete Success',
              'code'=>200 
            ]
            );
        }else{
          return response()->json(
            [
              'message'=> "Data dengan ID = $id Tidak Ditemukan",
              'code'=>404
            ]
            );
        }
      
  }
  
  
  public function mytrolleygrandtotal(Request $request){
    
         $cek=Tobasket::where('id_member',$request->idmember)->get();
      $jumlahtroli=0;
      if($cek){
          $data=Tobasket::where('id_member',$request->idmember)
            ->sum('subtotal');;
            $jumlahtroli=$data;  
        return response()->json([
                'value'=>'200',
                'jumlah' =>$jumlahtroli,
        ]);
      }else{
          return response()->json([
                'value'=>'205',
                'jumlah' =>0,
        ]);
      }
      
  }
  
  public function mytrolley(Request $request){
    
      
      
      $data=Tobasket::Join('data_pohon','data_basket.id_pohon','=','data_pohon.idpohon')
       ->where('data_basket.id_member',$request->idmember)
       ->get(['data_basket.*','data_pohon.localname','data_pohon.desa','data_pohon.foto_pohon','data_pohon.harga']);
   
        if(count($data)>0){
                 $items = array();
                 foreach ($data as $k) {
                 $b['id'] = $k->id;
                 $b['idpohon'] = $k->id_pohon;
                 $b['years'] = $k->y;
                 $b['pesan'] = $k->pesan;
                 $b['tanggal'] = $k->tanggal;
                 $b['localname'] = $k->localname;
                 $b['desa'] = $k->desa;
                 $b['foto_pohon'] = $k->foto_pohon;
                 $b['harga'] = $k->harga;
                 $b['subtotal'] = $k->subtotal;
                 
                 array_push($items, $b);
        }
        return response()->json($items);
        }else {
          $items = array();
            array_push($items);
            return response()->json($items);
            
             //return response()->json([]);
            
            
            
        }
      
  }
  
  
  
  
  public function gettroley(Request $request){
      $cek=Tobasket::where('id_member',$request->idmember)->get();
      $jumlahtroli=0;
      if($cek){
          $data=Tobasket::where('id_member',$request->idmember)->count();
            $jumlahtroli=$data;  
        return response()->json([
                'value'=>'200',
                'jumlah' =>$jumlahtroli,
        ]);
      }else{
          return response()->json([
                'value'=>'200',
                'jumlah' =>0,
        ]);
      }
      
  }
  
  public function pohonimage(Request $request){
      $data=Pohonimage::where('idpohon',$request->idpohon)->orderBy('id')->get();
      if(count($data)>0){
    $items = array();
    foreach ($data as $k) {
      // id disertakan agar admin web bisa hapus foto (hapuspohonimage).
      $b['id'] = $k->id;
      $b['urlnya'] = $k->urlnya;

      array_push($items, $b);
    }
    return response()->json($items);
  }else {
    $items = array();
    array_push($items);
    return response()->json($items);
  }
  }

  // Tambah foto galeri pohon (multipart): file disimpan di server, baris
  // imagepohon diisi URL self-hosted.
  public function uploadimage(Request $request){
    $idpohon = trim((string)$request->input('idpohon'));
    if ($idpohon === '') {
      return response()->json(['value' => '400', 'pesan' => 'idpohon wajib diisi'], 400);
    }
    if (!Pohon::where('idpohon', $idpohon)->exists()) {
      return response()->json(['value' => '404', 'pesan' => 'Pohon tidak ditemukan'], 404);
    }
    if (!$request->hasFile('image')) {
      return response()->json(['value' => '400', 'pesan' => 'No file uploaded'], 400);
    }

    $file=$request->file('image');
    $ext=strtolower($file->getClientOriginalExtension());
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
      return response()->json(['value' => '400', 'pesan' => 'Ekstensi harus jpg/jpeg/png/webp'], 400);
    }
    if ($file->getSize() > 2 * 1024 * 1024) {
      return response()->json(['value' => '400', 'pesan' => 'Ukuran maksimal 2MB'], 400);
    }

    $name_file='pohon_'.time().'_'.strtoupper(substr(md5(uniqid(rand(), true)), 0, 6)).'.'.$ext;
    $destinationPath=base_path('public/upload/pohon');
    if (!file_exists($destinationPath)) {
      mkdir($destinationPath, 0775, true);
    }
    $file->move($destinationPath, $name_file);

    $url=$request->getSchemeAndHttpHost().str_replace('/index.php', '', $request->getBaseUrl()).'/upload/pohon/'.$name_file;

    $data=new Pohonimage();
    $data->idpohon=$idpohon;
    $data->urlnya=$url;
    $data->save();

    return response()->json([
        'value'=>'200',
        'pesan'=>'Success',
        'url'=>$url,
        'id'=>$data->id,
    ]);
  }

  // Hapus satu foto galeri pohon by id baris imagepohon.
  public function hapuspohonimage(Request $request){
    $row=Pohonimage::find($request->id);
    if (!$row) {
      return response()->json(['value' => '404', 'pesan' => 'Foto tidak ditemukan'], 404);
    }
    $row->delete();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }


  public function updateforcertificate(Request $request){
    $id=$request->id;
    //$idpohon=$request->idpohon;



    $data=Dataadopsi::find($id);
    $iduser=$data->pengasuh;

    $data->proses=0;
    $data->update();
    
     $data=new Pesan();
        $data->idmember=$iduser;
        $data->pesan="Selamat Pohon anda telah selesai di taging certificat anda dapat di download";
        $data->status="noread";
        $data->save();

    $this->kirimPush($iduser, 'Certificat Received',
        'Selamat Pohon anda telah selesai di taging certificat anda dapat di download');




  }





  public function updatestatuscomplate(Request $request){
   
    $id=$request->id;
    $idpohon=$request->idpohon;
    
      

      $data=Dataadopsi::find($id);
    $statuspohon=Pohon::where('idpohon',$idpohon)->first();
    
        $data1=new Pesan();
        $data1->idmember=$data->pengasuh;
        $data1->pesan="Pohon anda telah selesai tagging";
        $data1->status="noread";
        $data1->save();
        $this->kirimPush($data->pengasuh, 'Tagging Selesai', 'Pohon anda telah selesai tagging');
    
    
  
    
    
    $statuspohon=Pohon::where('idpohon',$idpohon)->first();
    $statuspohon->adopted="adopted";
    $statuspohon->update();
    $data->proses=3;
    $data->update();

    // beri tahu semua admin: tagging pohon selesai (push FCM via kirimPush —
    // API legacy HTTP FCM sudah dimatikan Google)
    $caripohon=Pohon::where('idpohon',$request->idpohon)->first();
    $namapohon=$caripohon->localname;
    $namadesa=$caripohon->desa;
    foreach (Member::where('admin',1)->get() as $k) {
        $this->kirimPush($k->id, 'Tagging Complate',
            "Kode {$idpohon}, Nama Pohon: {$namapohon}, di desa {$namadesa}, Tagging Complate");
    }








    return response()->json([ 
      'value' =>"200",
      'message'=>"Tagging Complate"
    ]);  
    
    

  }
  
  


  public function getdetailimage(Request $request){
   // Baris catatan-saja (urlnya null, lihat updatenoted) bukan foto —
   // disaring agar app lama tidak merender gambar kosong.
   $data=Detailimage::where('idorder',$request->idorder)
     ->whereNotNull('urlnya')
     ->where('urlnya','!=','')
     ->get();
   if(count($data)>0){
    $items = array();
    foreach ($data as $k) {
      $b['urlnya'] = $k->urlnya;
     
      array_push($items, $b);
    }
    return response()->json($items);
  }else {
    $items = array();
    array_push($items);
    return response()->json($items);
  }

  }

  public function ceklatlangpohon(Request $request){
    $data=Pohon::where('idpohon',$request->idpohon)->first();
    if($data){
      return response()->json([
        'value'=>'200',
        'id' =>$data->id,
        'latitude' =>$data->latitude,
        'longitude' =>$data->longitude,
        
         ]);
    }else{
      return response()->json([
        'value'=>'0',
        'id' =>"",
        'latitude' =>"",
        'longitude' =>"",
        ]);
    }
  }



  public function updatestatusproses(Request $request){
   
    $id=$request->id;
    $data=Dataadopsi::find($id);
    
        $data1=new Pesan();
        $data1->idmember=$data->pengasuh;
        $data1->pesan="Pohon Anda Sedang Dalam Proses Taging";
        $data1->status="noread";
        $data1->save();
        $this->kirimPush($data->pengasuh, 'Proses Taging', 'Pohon Anda Sedang Dalam Proses Taging');
    
    
    $data->proses=2;
    $data->update();
    return response()->json([
      'value' =>"200",
      'message'=>"Update Status"
    ]);  

  }

  public function ordercustomer(Request $request){
     $data = Dataadopsi::join('data_pohon', 'data_adopsi.idpohon', '=', 'data_pohon.idpohon')
        ->whereIn('data_adopsi.proses', [1, 2, 3])
        ->selectRaw('
            data_adopsi.id,
            data_adopsi.idpohon,
            data_adopsi.pengasuh,
            data_adopsi.nama,
            data_adopsi.price,
            data_adopsi.cur,
            data_adopsi.methode,
            data_adopsi.tgl_adopt,
            data_adopsi.gfrom,
            data_adopsi.certnum,
            data_adopsi.dur,
            data_adopsi.memo,
            data_adopsi.admin,
            data_adopsi.proses,
            data_adopsi.invoice,
            data_pohon.localname,
            data_pohon.foto_pohon,
            data_pohon.latitude,
            data_pohon.longitude,
            data_pohon.diameter,
            data_pohon.tinggi,
            data_pohon.keliling,
            data_adopsi.created_at,
            data_adopsi.updated_at,
            data_adopsi.tgl_exp,
            data_adopsi.desa
        ')
        ->distinct() // Hindari duplikasi
        ->orderBy('data_adopsi.tgl_adopt', 'desc')
        ->orderBy('data_adopsi.id', 'desc')
        ->get();

    // Jika data ditemukan
    if ($data->count() > 0) {
        $items = [];

        foreach ($data as $k) {
            // Satu baris per pohon; order multi-pohon = beberapa baris invoice sama.
            // Duplikasi dengan distinct() di query sudah tercakup — jangan dedupe
            // per invoice di sini atau pohon lain dalam order yang sama hilang.

            // Ambil data konfirmasi (hanya satu baris)
            $cekconfirm = Confirmasi::where('invoice', $k->invoice)->first();
            $confirm = $cekconfirm ? $cekconfirm->confirmation : "";

            // Ambil nama yang melakukan konfirmasi
            $confirmBy = "";
            if ($cekconfirm) {
                $ambilnamaconfirmasi = Member::where('id', $cekconfirm->confirmationBy)->first();
                $confirmBy = $ambilnamaconfirmasi ? $ambilnamaconfirmasi->name : "";
            }

            // Ambil ID konfirmasi (hanya satu data)
            $idconfirmasi = $cekconfirm ? $cekconfirm->id : 0;

            // Ambil foto transaksi jika ada
            $fotopembayaran = $cekconfirm ? $cekconfirm->foto : "";

            // Format tanggal
            $created_at_formatted = $k->created_at ? $k->created_at->format('d-M-Y H:i:s') : "";

            // Simpan data ke dalam array
            $b = [
                'id' => $k->id,
                'idpohon' => $k->idpohon,
                'pengasuh' => $k->pengasuh,
                'nama' => $k->nama,
                'price' => $k->price,
                'cur' => $k->cur,
                'methode' => $k->methode,
                'tgl_adopt' => $k->tgl_adopt,
                'gfrom' => $k->gfrom,
                'certnum' => $k->certnum,
                'dur' => $k->dur,
                'memo' => $k->memo,
                'admin' => $k->admin,
                'proses' => $k->proses,
                'invoice' => $k->invoice,
                'confirmasi' => $confirm,
                'confirmasiBy' => $confirmBy,
                'confirmasiid' => $idconfirmasi,
                'localname' => $k->localname,
                'created_at' => $created_at_formatted,
                'foto_pohon' => $k->foto_pohon,
                'latitude' => $k->latitude,
                'longitude' => $k->longitude,
                'diameter' => $k->diameter,
                'tinggi' => $k->tinggi,
                'keliling' => $k->keliling,
                'tgl_exp' => $k->tgl_exp,
                'foto' => $fotopembayaran,
                'fotopembayaran' => !empty($fotopembayaran) ? $request->getSchemeAndHttpHost().str_replace('/index.php', '', $request->getBaseUrl()).'/upload/slider/'.$fotopembayaran  : "",
                'desa' => $k->desa,
                // Total dari tabel confirmation (sudah termasuk kode unik) dan
                // jumlah pohon per invoice — dipakai app utk mengelompokkan order.
                'total' => $cekconfirm ? $cekconfirm->price : $k->price,
                'jmlpohon' => $cekconfirm ? (int) $cekconfirm->jml_pohon : 1,
                // Waktu terakhir baris adopsi di-update (mis. petugas mengubah
                // proses taging) + jumlah foto taging pohon ini (konsisten
                // dgn lihatfototaging yang query per idpohon).
                'updated_at' => $k->updated_at ? $k->updated_at->format('d-M-Y H:i') : "",
                'jmlfoto' => Fototaging::where('idpohon', $k->idpohon)->count()
            ];

            array_push($items, $b);
        }

        return response()->json($items);
    } else {
        return response()->json([]);
    }

  }


    
  public function ordercustomerbypengurus(Request $request){
    
    // Semua desa yang diampu petugas (pivot desa_petugas, banyak-ke-banyak).
    $desaAmpu=DesaPetugas::where('idpetugas',$request->iduser)->pluck('iddesa');
    $namadesaList=Desa::whereIn('id',$desaAmpu)->pluck('nama');
    $namadesa=$namadesaList->first();
    $namadesa=$namadesa ? $namadesa : "";
    

    
    
    
    
    $data=Dataadopsi::join('data_pohon','data_adopsi.idpohon','=','data_pohon.idpohon')
        
      ->whereIn('data_adopsi.proses',[1,2,3])
      ->whereIn('data_adopsi.desa',$namadesaList)

      ->orderBy('data_adopsi.tgl_adopt','desc')
      ->orderBy('data_adopsi.id','desc')
    ->get(['data_adopsi.*','data_pohon.localname','data_pohon.foto_pohon','data_pohon.latitude','data_pohon.longitude','data_pohon.diameter','data_pohon.tinggi','data_pohon.keliling']);
    if(count($data)>0){
      // Peta desa (nama → row) untuk lokasi lengkap di papan taging.
      $desaMap = Desa::whereIn('nama', $namadesaList)->get()->keyBy('nama');
      $items = array();
      foreach ($data as $k) {
        $desaRow = $desaMap->get($k->desa);
        $b['id'] = $k->id;
        $b['idpohon'] = $k->idpohon;
        $b['pengasuh']=$k->pengasuh;
        $b['nama']=$k->nama;
        $b['price']=$k->price;
        $b['cur']=$k->cur;
        $b['methode']=$k->methode;
        $b['tgl_adopt']=$k->tgl_adopt;
        $b['gfrom']=$k->gfrom;
        $b['certnum']=$k->certnum;
        $b['dur']=$k->dur;
        $b['memo']=$k->memo;
        $b['admin']=$k->admin;
        $b['proses']=$k->proses;
        $b['invoice']=$k->invoice;
       
       
       $cekconfirm=Confirmasi::where('invoice',$k->invoice)->first();
          if($cekconfirm){
        $confirm=$cekconfirm->confirmation;
        
        $ambilnamaconfirmasi=Member::where('id',$cekconfirm->confirmationBy)->first();
        if($ambilnamaconfirmasi){
        $namanya=$ambilnamaconfirmasi->name;    
        $b['confirmasiBy']=$namanya;  
        }else{
            $namanya="";
            $b['confirmasiBy']=$namanya;  
        }
        
        
        
        $confirmBy=$cekconfirm->confirmationBy;
        
        }else{
            $confirm="";    
        }
        
        $idconfirmasi=Confirmasi::where('invoice',$k->invoice)->first();
          if($idconfirmasi){
        $confirmid=$idconfirmasi->id;    
        }else{
            $confirmid=0;    
        }
              
        $b['confirmasi']=$confirm;
        $b['localname']=$k->localname;
        $b['created_at']=$k->created_at->format('d-M-Y h:m:s');
        $b['foto_pohon']=$k->foto_pohon;
        $b['latitude']=$k->latitude;
        $b['longitude']=$k->longitude;
        $b['diameter']=$k->diameter;
        $b['tinggi']=$k->tinggi;
        $b['keliling']=$k->keliling;
        $b['tgl_exp']=$k->tgl_exp;
        
        
        $carifototransaksi=Confirmasi::where('invoice',$k->invoice)->first();
        
        if($carifototransaksi){
        $foto=$carifototransaksi->foto;    
        }else{
            $foto="";    
        }
        
         $cariid=Confirmasi::where('invoice',$k->invoice)->first();
        
        if($cariid){
        $idnya=$cariid->id;    
        }else{
            $idnya=0;    
        }
        
        $b['foto']=$foto;
        $b['idconfirmasi']=$idnya;
        
         $cekfoto=Confirmasi::where('invoice',$k->invoice)->first();
          if($cekfoto){
        $fotopembayaran=$cekfoto->foto;    
        }else{
            $fotopembayaran="";    
        }
         
         
        // dd($cekfoto);
       
          $b['fotopembayaran'] = !empty($fotopembayaran)
              ? $request->getSchemeAndHttpHost().str_replace('/index.php', '', $request->getBaseUrl()).'/upload/slider/'.$fotopembayaran
              : "";
          $b['desa']=$k->desa;
          $b['kecamatan']=$desaRow ? $desaRow->kecamatan : "";
          $b['kabupaten']=$desaRow ? $desaRow->kabupaten : "";
          $b['provinsi']=$desaRow ? $desaRow->provinsi : "";
          $b['confirmasiid']=$confirmid;
        

        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }

  }





  public function sendNotifkepengurus(Request $request){

    $idpohon=$request->idpohon;
    $caripohon=Pohon::where('idpohon',$request->idpohon)->first();
    $caripohon->adopted="adopted";
    $caripohon->update();
    
    
    
    $namapohon=$caripohon->localname;
    $namadesa=$caripohon->desa;
    $caripengurus=Desa::where('nama',$namadesa)->first();

    // Kirim ke SEMUA petugas desa ini (pivot desa_petugas).
    if ($caripengurus) {
        $semuapetugas=DesaPetugas::where('iddesa',$caripengurus->id)->pluck('idpetugas');
        foreach ($semuapetugas as $idpengurus) {
            $carimember=Member::find($idpengurus);
            if (!$carimember) { continue; }

            $isip = "Permintaan Taging kode {$idpohon}, Nama Pohon: {$namapohon}";
            $pesan=new Pesan();
            $pesan->idmember=$idpengurus;
            $pesan->pesan=$isip;
            $pesan->status="noread";
            $pesan->save();
            $this->kirimPush($idpengurus, 'Taging Pohon', $isip);
        }
    }


}



  public function sendNotif(Request $request){
      $this->notifikasiCheckoutAdmin($request->iduser);
}



    public function mytrees(Request $request){
      $iduser=$request->iduser;
      $data=Dataadopsi::join('data_pohon','data_adopsi.idpohon','=','data_pohon.idpohon')
     // ->join('desa','data_adopsi.desa','=','desa.nama')
      ->where('data_adopsi.pengasuh',$iduser)
      ->orderBy('data_adopsi.tgl_adopt','desc')
      ->orderBy('data_adopsi.id','desc')
      ->get(['data_adopsi.*','data_pohon.localname','data_pohon.foto_pohon']);
      if(count($data)>0){
        $items = array();
        foreach ($data as $k) {
          $b['id'] = $k->id;
          $b['idpohon'] = $k->idpohon;
          $b['pengasuh']=$k->pengasuh;
          $b['nama']=$k->nama;
          $b['price']=$k->price;
          $b['cur']=$k->cur;
          $b['methode']=$k->methode;
          $b['tgl_adopt']=$k->tgl_adopt;
          $b['gfrom']=$k->gfrom;
          $b['certnum']=$k->certnum;
          $b['dur']=$k->dur;
          $b['memo']=$k->memo;
          $b['admin']=$k->admin;
          $b['proses']=$k->proses;
          $b['invoice']=$k->invoice;
          $b['localname']=$k->localname;
          $b['created_at']=$k->created_at->format('d-M-Y h:m:s');
          $b['foto_pohon']=$k->foto_pohon;
          $b['desa']=$k->desa;
          $b['tgl_exp']=$k->tgl_exp;
          $cekconfirm=Confirmasi::where('invoice',$k->invoice)->first();
          if($cekconfirm){
              $a=$cekconfirm->confirmation;
          }else{
              $a="";
          }
       
          $b['confirm']=$a;
          
          
          array_push($items, $b);
        }
        return response()->json($items);
      }else {
        $items = array();
        array_push($items);
        return response()->json($items);
      }




    }




  public function adopsi(Request $request){
    $tanggal=Date('Y-m-d');
    $cari=Pohon::where('id',$request->id)->first();
    $idpohon=$cari->idpohon;
    $desa=$cari->desa;
 

    $carimember=Member::where('id',$request->iduser)->first();
    $nama=$carimember->name;
    
    $data=new Dataadopsi();
    $data->idpohon=$idpohon;
    $data->desa=$desa;
    $data->pengasuh=$request->input('iduser');
    $data->nama=$nama;
    $data->price=$request->input('price');
    $data->cur="IDR";
    $data->methode="Transfer";
    $data->tgl_adopt=$tanggal;
    $data->gfrom=0;
    $data->certnum="";
    $data->memo=$request->input('memo');
    $data->admin=0;
    $data->proses=1;
    $data->invoice=0;
    $data->dur=$request->input('dur');
    $data->save(); 
    return response()->json([
      'value' =>"200",
      'message'=>"Successfully added to the basket please make payment"
    ]);   



  }


  public function toadopt(Request $request){

    $tanggal=Date('Y-m-d');
    $data=new Tobasket();
    $data->id_pohon=$request->input('id_pohon');
    $data->id_member=$request->input('id_member');
    $data->y=$request->input('years');
    
    
    
    
    $data->gift_to=0;
    $data->pesan=$request->input('pesan');
    $data->tanggal=$tanggal;
    $data->nama=$request->input('nama');
    $data->kurs=14000;
    $harga=Datapohon::where('idpohon',$request->input('id_pohon'))->first();
    dd($harga->harga);
    
    
    
    $data->subtotal=$request->input('years');
    
    
    
    $data->save();
    return response()->json([
      'code' =>"200",
      'message'=>"Successfully added to the basket please make payment"
     
       ]);


  }


  public function getnoadmin(){
    $data=Noadmin::all();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['nomer_admin'] = $k->nomer_admin;
                      
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
  }

  // ===== CRUD nomor WA admin (tabel kontak_admin) =====

  public function tambahnoadmin(Request $request){
    $nomer = trim((string)$request->input('nomer'));
    if ($nomer === '') {
      return response()->json(['value' => '400', 'pesan' => 'Nomor WA wajib diisi'], 400);
    }
    $n = new Noadmin();
    $n->nomer_admin = $nomer;
    $n->save();
    return response()->json(['value' => '200', 'pesan' => 'Success', 'id' => $n->id]);
  }

  public function editnoadmin(Request $request){
    $n = Noadmin::find($request->id);
    if (!$n) {
      return response()->json(['value' => '404', 'pesan' => 'Nomor tidak ditemukan'], 404);
    }
    $nomer = trim((string)$request->input('nomer'));
    if ($nomer === '') {
      return response()->json(['value' => '400', 'pesan' => 'Nomor WA wajib diisi'], 400);
    }
    $n->nomer_admin = $nomer;
    $n->save();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }

  public function hapusnoadmin(Request $request){
    $n = Noadmin::find($request->id);
    if (!$n) {
      return response()->json(['value' => '404', 'pesan' => 'Nomor tidak ditemukan'], 404);
    }
    $n->delete();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }




  public function tobasket(Request $request){
    
   
    $cektroli=Tobasket::where([['id_pohon',$request->input('id_pohon')],['id_member',$request->input('id_member')]])->first();
    
    if($cektroli){
          return response()->json([
      'code' =>"205",
      'message'=>"Allready To My Basket"
     
       ]);
    }else{
        
    $tanggal=Date('Y-m-d');
    $data=new Tobasket();
    $data->id_pohon=$request->input('id_pohon');
    $data->id_member=$request->input('id_member');
    $data->y=$request->input('years');
    $data->gift_to=0;
    // '' dikonversi middleware jadi NULL → kolom NOT NULL menolak; adopsi
    // non-hadiah mengirim nama/pesan kosong.
    $data->pesan=$request->input('pesan') ?? '';
    $data->tanggal=$tanggal;
    $data->nama=$request->input('nama') ?? '';
    $data->kurs=14000;
    $harga=Pohon::where('idpohon',$request->input('id_pohon'))->first();
    $data->subtotal=$request->input('years')*$harga->harga;
    $data->save();
    return response()->json([
      'code' =>"200",
      'message'=>"Successfully added to the basket please make payment"
     
       ]);
    }




  }


  public function getrekening(){
    $data=Rekening::all();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['atas_nama'] = $k->atas_nama;
        $b['nama_bank']=$k->nama_bank;
        $b['no_rek']=$k->no_rek;
        $b['icon']=$k->icon;
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
  

  }
  
  public function getpohondesafilter(Request $request){
    $desa=$request->namadesa;
    $status=$request->status;
    $data=Pohon::where([['desa',$desa],['adopted',$status]])->get();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
         $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        $b['tgl_exp'] = $exp;
       
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }

  }


  public function getpohondesa(Request $request){
    $desa=$request->namadesa;
    $status=$request->status;
    $data=Pohon::where('desa',$desa)->get();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
          $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
         $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        $b['tgl_exp'] = $exp;
       
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }

  }


  public function getdesa(){

    $data=Desa::all();
    if(count($data)>0){
      // Hitung semua desa dalam 1 query agregat (dulu: 3 query count
      // full-scan per desa → puluhan detik saat cache DB dingin).
      $counts = Pohon::selectRaw(
        "desa, SUM(adopted = 'adopted') AS j_adopted, SUM(adopted = 'available') AS j_available, COUNT(*) AS j_total"
      )->groupBy('desa')->get()->keyBy('desa');

      $adminIds = $data->pluck('admin_desa')->unique()->filter();
      $admins = Member::whereIn('id', $adminIds)->pluck('name', 'id');

      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['nama'] = $k->nama;
        $b['profil']=$k->profil;
        $b['latitude']=$k->latitude;
        $b['longitude']=$k->longitude;
        $b['foto']=$k->foto;
        $b['hutan_desa']=$k->hutan_desa;

        $c = $counts->get($k->nama);
        $b['adopted']=$c ? (int)$c->j_adopted : 0;

        $b['available']=$c ? (int)$c->j_available : 0;

        $b['total']=$c ? (int)$c->j_total : 0;

        $b['provinsi']=$k->provinsi;
        $b['kecamatan']=$k->kecamatan;
        $b['kabupaten']=$k->kabupaten;
        $b['admin_desa']=$admins[$k->admin_desa] ?? "-";
        $b['label']=$k->label;
        $b['kode_cert']=$k->kode_cert;
        $b['kode_pohon']=$k->kode_pohon;
        $b['skema']=$k->skema;
        $b['aktif']=(int)$k->aktif;

        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
  }

  // ==== CRUD Data Lokasi (kelola desa/lokasi web admin) ====
  // Kolom desa di luar fillable model → assign property per-field.

  public function tambahlokasi(Request $request){
    $nama = strtolower(trim((string)$request->input('nama')));
    if ($nama === '') {
      return response()->json(['value'=>400,'pesan'=>'Nama desa wajib diisi']);
    }
    if (Desa::where('nama',$nama)->exists()) {
      return response()->json(['value'=>400,'pesan'=>'Nama desa sudah dipakai']);
    }
    $d = new Desa;
    $d->nama = $nama;
    $d->label = $request->input('label');
    $d->kode_cert = $request->input('kode_cert');
    $d->kode_pohon = $request->input('kode_pohon');
    $d->kecamatan = $request->input('kecamatan');
    $d->kabupaten = $request->input('kabupaten');
    $d->provinsi = $request->input('provinsi');
    $d->skema = $request->input('skema');
    $d->latitude = $request->input('latitude');
    $d->longitude = $request->input('longitude');
    $d->profil = $request->input('profil');
    $d->foto = $request->input('foto');
    $d->aktif = (int)$request->input('aktif', 1) === 1 ? 1 : 0;
    $d->save();
    return response()->json(['value'=>200,'id'=>$d->id,'pesan'=>'Lokasi ditambahkan']);
  }

  public function editlokasi(Request $request){
    $id = $request->input('id');
    $d = Desa::find($id);
    if (!$d) {
      return response()->json(['value'=>404,'pesan'=>'Lokasi tidak ditemukan']);
    }
    $namaBaru = strtolower(trim((string)$request->input('nama')));
    $ubahNama = $request->has('nama') && $namaBaru !== '' && $namaBaru !== $d->nama;
    if ($ubahNama && Desa::where('nama',$namaBaru)->where('id','!=',$id)->exists()) {
      return response()->json(['value'=>400,'pesan'=>'Nama desa sudah dipakai']);
    }

    DB::transaction(function () use ($d, $request, $ubahNama, $namaBaru) {
      $namaLama = $d->nama;
      if ($request->has('nama') && $namaBaru !== '') $d->nama = $namaBaru;
      if ($request->has('label')) $d->label = $request->input('label');
      if ($request->has('kode_cert')) $d->kode_cert = $request->input('kode_cert');
      if ($request->has('kode_pohon')) $d->kode_pohon = $request->input('kode_pohon');
      if ($request->has('kecamatan')) $d->kecamatan = $request->input('kecamatan');
      if ($request->has('kabupaten')) $d->kabupaten = $request->input('kabupaten');
      if ($request->has('provinsi')) $d->provinsi = $request->input('provinsi');
      if ($request->has('skema')) $d->skema = $request->input('skema');
      if ($request->has('latitude')) $d->latitude = $request->input('latitude');
      if ($request->has('longitude')) $d->longitude = $request->input('longitude');
      if ($request->has('profil')) $d->profil = $request->input('profil');
      if ($request->has('foto')) $d->foto = $request->input('foto');
      if ($request->has('aktif')) $d->aktif = (int)$request->input('aktif') === 1 ? 1 : 0;
      $d->save();

      // nama desa = string join di data_pohon & data_adopsi → ikut dipindahkan
      if ($ubahNama) {
        Pohon::where('desa',$namaLama)->update(['desa'=>$namaBaru]);
        Dataadopsi::where('desa',$namaLama)->update(['desa'=>$namaBaru]);
      }
    });
    return response()->json(['value'=>200,'pesan'=>'Lokasi diperbarui']);
  }

  public function hapuslokasi(Request $request){
    $id = $request->input('id');
    $d = Desa::find($id);
    if (!$d) {
      return response()->json(['value'=>404,'pesan'=>'Lokasi tidak ditemukan']);
    }
    $jml = Pohon::where('desa',$d->nama)->count();
    if ($jml > 0) {
      return response()->json(['value'=>409,'pesan'=>"Masih ada {$jml} pohon di lokasi ini — pindahkan/hapus pohonnya dulu"]);
    }
    DB::transaction(function () use ($d) {
      DesaPetugas::where('iddesa',$d->id)->delete();
      $d->delete();
    });
    return response()->json(['value'=>200,'pesan'=>'Lokasi dihapus']);
  }

//untuk mengambiil data profil
  public function getprofil(Request $request){
        $id=$request->id;
      //  $data=Blog::join('member','blog.idmember','=','member.id')
        $data=Member::join('desa_petugas','desa_petugas.idpetugas','=','member.id')
            ->join('desa','desa.id','=','desa_petugas.iddesa')
            ->where("member.id",$id)->first();
        if($data){
          return response()->json([
            'id' =>$data->id ,
            'name' => $data->name,
            'emaile' => $data->emaile,
            'hp' => $data->hp,
            'admin' => $data->admin,
            'desa' => $data->nama,
            
            
             ]);
        }else{
            
              $data1=Member::where("member.id",$id)->first();
            if($data1){
                   return response()->json([
            'id' =>$data1->id ,
            'name' => $data1->name,
            'emaile' => $data1->emaile,
            'hp' => $data1->hp,
            'admin' => $data1->admin,
            
            
            
             ]);
            }
            
            
          return response()->json([
            'id' =>"",
            'name' =>"",
            'emaile' =>"",
            'hp' =>"",
            
             ]);
        }


  }


  public function filtertrees(Request $request){
    $status=$request->adopted;
    $data=Pohon::where('adopted',$status)->get();

    if(count($data)>0){
      // Dulu: 2 query tambahan per pohon (nama pengasuh + tgl_exp) → 6000+
      // query untuk 3000 pohon. Sekarang cukup 2 query whereIn besar.
      $pengasuhIds = $data->pluck('pengasuh')->unique()->filter();
      $namaPengasuh = Member::whereIn('id', $pengasuhIds)->pluck('name', 'id')->all();

      // first() dulu = baris data_adopsi ber-id terkecil per idpohon.
      $expMap = [];
      foreach (Dataadopsi::whereIn('idpohon', $data->pluck('idpohon')->unique())->orderBy('id')->get() as $row) {
        if (!array_key_exists($row->idpohon, $expMap)) {
          $expMap[$row->idpohon] = $row->tgl_exp;
        }
      }

      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        $b['pengasuh'] = array_key_exists($k->pengasuh, $namaPengasuh) ? $namaPengasuh[$k->pengasuh] : "-";
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
        $b['tgl_exp'] = array_key_exists($k->idpohon, $expMap) ? $expMap[$k->idpohon] : "-";
       
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
}

  public function slider(){
    $data=Slider::all();
    /*
    $items = [];

    if ($data->count() > 0) {
        foreach ($data as $k) {
            $items[] = [
                'id' => $k->id,
                'judul' => $k->judul,
                'deskripsi' => $k->deskripsi,
                'gambar' => asset('assets/' . $k->gambar), // otomatis pakai base URL https kalau sudah SSL
            ];
        }
    }

    return response()->json($items, 200, [
        'Cache-Control' => 'max-age=86400, public',
        'Content-Type' => 'application/json'
    ]);
    
    */
    
    
    if(count($data)>0){
      $items=array();
      foreach($data as $k){
        $b['id'] = $k->id;
        $b['judul'] = $k->judul;
        $b['deskripsi'] = $k->deskripsi;
        $b['gambar'] = 'https://rest.pohonasuh.org/assets/' .$k->gambar;

        array_push($items, $b);

      }
      return response()->json($items);
    }else{
      $items = array();
      array_push($items);
      return response()->json($items);
    }
    
    
    
  }



  public function pohonhighlight(){
    $data=Pohon::where([['highlight',1],['adopted','available']])->get();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        
          $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
           $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        
        
        $b['tgl_exp'] = $exp;
       
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
}






  public function pohoncari(Request $request){
   // $data=Pohon::where([['highlight',2],['adopted','available']])->get();
     // Mendapatkan keyword pencarian dari request
    $keyword = $request->input('keyword', '');

    // Query dengan kondisi highlight, adopted, dan pencarian desa
    $data = Pohon::where('highlight', 2)
                 ->where('adopted', 'available')
                 ->when($keyword, function ($query, $keyword) {
                     return $query->where('desa', 'like', '%' . $keyword . '%');
                 })
                 ->get();

    
    
  //  $data=Pohon::all();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        
        $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
          $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        
        
        $b['tgl_exp'] = $exp;
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
}



  public function pohon(Request $request){
   // $data=Pohon::where([['highlight',2],['adopted','available']])->get();

      $keyword = $request->input('keyword', '');

    // Query dengan kondisi highlight, adopted, dan pencarian desa
    $data = Pohon::where('highlight', 2)
                 ->where('adopted', 'available')
                 ->when($keyword, function ($query, $keyword) {
                     return $query->where('desa', 'like', '%' . $keyword . '%');
                 })
                 ->get();



    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        
        $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
          $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        
        
        $b['tgl_exp'] = $exp;
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
}


  public function pohonbydesa(Request $request){
      $desa=$request->desa;
      
    $data=Pohon::where([['adopted','available'],['desa',$desa]])->get();
  //  $data=Pohon::all();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        
        $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
        
          $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        
        
        $b['tgl_exp'] = $exp;
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
}



  public function pohonmap(Request $request){
      
      
   $jumlah=$request->input('limit');
   if($jumlah==0){
         $data=Pohon::all();
   }else{
         $data=Pohon::all()->take($jumlah);
   }
    
  
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        
        $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
          $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        
        
        $b['tgl_exp'] = $exp;
       // $b['location'] = (float)$k->latitude . "," . (float)$k->longitude;
               $b['location'] = [
                'latitude' => (float) $k->latitude,
                'longitude' => (float) $k->longitude
            ];
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
}


  public function pohonmapall(){
     
    //$data=Pohon::where([['highlight',2],['adopted','available']])->get();
    $data=Pohon::all();
    if(count($data)>0){
      $items = array();
      foreach ($data as $k) {
        $b['id'] = $k->id;
        $b['gpscode'] = $k->gpscode;
        $b['latitude'] = $k->latitude;
        $b['longitude'] = $k->longitude;
        $b['desa'] = $k->desa;
        $b['idpohon'] = $k->idpohon;
        $b['species'] = $k->species;
        $b['family'] = $k->family;
        $b['localname'] = $k->localname;
        $b['status'] = $k->status;
        $b['jenis'] = $k->jenis;
        $b['diameter'] = $k->diameter;
        $b['tinggi'] = $k->tinggi;
        $b['keliling'] = $k->keliling;
        $b['dpl'] = $k->dpl;
        $b['slope'] = $k->slope;
        $b['manfaat'] = $k->manfaat;
        $b['soil'] = $k->soil;
        $b['surveyor'] = $k->surveyor;
        $b['tgl_survey'] = $k->tgl_survey;
        $b['fotografer'] = $k->fotografer;
        $b['dilihat'] = $k->dilihat;
        $b['hit'] = $k->hit;
        $b['beku'] = $k->beku;
        $b['score'] = $k->score;
        $b['tgl_pesan'] = $k->tgl_pesan;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['price'] = $k->price;
        $b['cur'] = $k->cur;
        $b['adopted'] = $k->adopted;
        $b['dur'] = $k->dur;
        $b['methode'] = $k->methode;
        
        $caripengasuh=Member::where("id",$k->pengasuh)->first();
        if($caripengasuh){
            $namapengasuh=$caripengasuh->name;
        }else{
            $namapengasuh="-";
        }
        
        $b['pengasuh'] = $namapengasuh;
        $b['gfrom'] = $k->gfrom;
        $b['nama'] = $k->nama;
        $b['catatan'] = $k->catatan;
        $b['admin'] = $k->admin;
        $b['proses'] = $k->proses;
        $b['keterangan'] = $k->keterangan;
        $b['qrcode'] = $k->qrcode;
        $b['harga'] = $k->harga;
        $b['asl'] = $k->asl;
        $b['foto_pohon'] = $k->foto_pohon;
        $b['tgl_adopt'] = $k->tgl_adopt;
        $b['highlight'] = $k->highlight;
          $cariexp=Dataadopsi::where("idpohon",$k->idpohon)->first();
        if($cariexp){
            $exp=$cariexp->tgl_exp;
        }else{
            $exp="-";
        }
        
        
        $b['tgl_exp'] = $exp;
                
        array_push($items, $b);
      }
      return response()->json($items);
    }else {
      $items = array();
      array_push($items);
      return response()->json($items);
    }
}

  



  // ===================== Integrasi website =====================
  // Satu pohon lengkap berdasarkan kode (halaman detail & admin web).
  public function pohonbykode(Request $request){
    $p = Pohon::where('idpohon', $request->input('idpohon'))->first();
    if (!$p) {
      return response()->json(null, 404);
    }
    return response()->json($p);
  }

  // Data sertifikat adopsi publik by certnum (halaman /sertifikat web).
  // Satu certnum menutup satu invoice (bisa >1 pohon) → jml_pohon dihitung
  // dari baris data_adopsi ber-certnum sama (lebih akurat daripada
  // confirmation.jml_pohon utk data lama). Fallback nama via member utk
  // baris lama yang kolom nama-nya kosong.
  public function sertifikatpublik($certnum){
    $da = Dataadopsi::join('desa', 'desa.nama', '=', 'data_adopsi.desa')
      ->join('data_pohon', 'data_pohon.idpohon', '=', 'data_adopsi.idpohon')
      ->where('data_adopsi.certnum', $certnum)
      ->first(['data_adopsi.*', 'desa.provinsi', 'desa.kabupaten', 'desa.kecamatan',
        'data_pohon.localname', 'data_pohon.species', 'data_pohon.foto_pohon']);
    if (!$da || trim((string)$da->certnum) === '') {
      return response()->json(null, 404);
    }
    $da->jml_pohon = Dataadopsi::where('certnum', $certnum)->count();
    $nama = trim((string) $da->nama);
    if ($nama === '' || $nama === '-') {
      $m = Member::find($da->pengasuh);
      $da->nama = $m ? $m->name : $nama;
    }
    return response()->json($da);
  }

  // Total donasi terverifikasi untuk halaman transparansi keuangan web.
  public function totaldonasi(){
    return response()->json([
      'total' => (int) Confirmasi::where('confirmation', 'yes')->sum('price'),
    ]);
  }

  // Tambah pohon baru dari form admin web. Kolom NOT NULL tanpa default
  // diisi nilai kosong agar insert tidak gagal.
  public function tambahpohon(Request $request){
    $idpohon = trim((string)$request->input('idpohon'));
    $localname = trim((string)$request->input('localname'));
    $desaNama = trim((string)$request->input('desa'));
    $harga = (int)$request->input('harga');
    if ($idpohon === '' || $localname === '' || $desaNama === '' || $harga <= 0) {
      return response()->json([
        'value' => '400',
        'pesan' => 'idpohon, localname, desa, dan harga wajib diisi',
      ], 400);
    }
    if (strlen($idpohon) > 7) {
      return response()->json([
        'value' => '400',
        'pesan' => 'Kode pohon maksimal 7 karakter',
      ], 400);
    }
    if (Pohon::where('idpohon', $idpohon)->exists()) {
      return response()->json([
        'value' => '409',
        'pesan' => 'Kode pohon sudah dipakai',
      ], 409);
    }
    if (!Desa::where('nama', $desaNama)->exists()) {
      return response()->json([
        'value' => '404',
        'pesan' => 'Desa tidak ditemukan',
      ], 404);
    }

    $p = new Pohon();
    $p->idpohon = $idpohon;
    $p->localname = $localname;
    $p->species = $request->input('species') ?? '';
    $p->family = $request->input('family') ?? '';
    $p->desa = $desaNama;
    $p->diameter = (int)($request->input('diameter') ?? 0);
    $p->tinggi = (int)($request->input('tinggi') ?? 0);
    $p->harga = $harga;
    $p->price = $harga;
    $p->cur = 'IDR';
    $p->adopted = 'available';
    $p->highlight = 2; // tampil di listing web/mobile
    $p->slope = '';
    $p->soil = '';
    $p->beku = 'no';
    $p->nama = '';
    $p->keterangan = '';
    $p->qrcode = '';
    [$fotoUrl, $fotoErr] = $this->prosesFotoPohon($request);
    if ($fotoErr) return $fotoErr;
    $p->foto_pohon = $fotoUrl ?? ($request->input('foto_pohon') ?? '');
    $p->save();

    return response()->json([
      'value' => '200',
      'pesan' => 'Success',
    ]);
  }


  public function addviewer(Request $request){
      $id=$request->id;
      $data=Blog::find($id);
      $viewersekarang=$data->viewer;
      $data->viewer=1+$viewersekarang;
      $data->update();
      return response()->json([
        'id' =>"success" ,
       
         ]);
  }


  public function blogfirst(Request $request){
   // $data=Blog::orderBy('id','DESC')->first();

     $data=Blog::join('member','blog.idmember','=','member.id')
      ->orderBy('id','DESC')
      ->first(['blog.*','member.name as nama']);

    if($data){
      return response()->json([
        'id' =>$data->id ,
        'name' => $data->name,
        'deskripsi' => $data->deskripsi,
        'viewer' => $data->viewer,
        'cover' => $data->cover ? $request->getSchemeAndHttpHost() . '/assets/' . $data->cover : '',
        'kategori' => $data->kategori,
        'nama' => $data->nama,
        
         ]);
    }else{
          return response()->json([
            'id' =>"",
            'name' => "",
            'deskripsi' =>"",
            'viewer' => "",
            'cover' => "",
            'kategori' => "",
             ]);
    }

}


  public function blogbyfilter(Request $request){
      $data=Blog::join('member','blog.idmember','=','member.id')
      ->where('kategori',$request->kategori)
      ->get(['blog.*','member.name as nama']);
      if(count($data)>0){
        $items = array();
        foreach ($data as $k) {
          $b['id'] = $k->id;
          $b['name'] = $k->name;
          $b['deskripsi'] = $k->deskripsi;
          $b['viewer'] = $k->viewer;
          $b['cover'] = $k->cover ? $request->getSchemeAndHttpHost() . '/assets/' . $k->cover : '';
          $b['kategori'] = $k->kategori;
          $b['nama'] = $k->nama;
          $b['created_at'] = $k->created_at->format('d/M/Y h:m:s');
                  
          array_push($items, $b);
        }
        return response()->json($items);
      }else {
        $items = array();
        array_push($items);
        return response()->json($items);
      }
  }




  public function blog(Request $request){
      $data=Blog::join('member','blog.idmember','=','member.id')->get(['blog.*','member.name as nama']);
      if(count($data)>0){
        $items = array();
        foreach ($data as $k) {
          $b['id'] = $k->id;
          $b['name'] = $k->name;
          $b['deskripsi'] = $k->deskripsi;
          $b['viewer'] = $k->viewer;
          $b['cover'] = $k->cover ? $request->getSchemeAndHttpHost() . '/assets/' . $k->cover : '';
          $b['kategori'] = $k->kategori;
          $b['nama'] = $k->nama;
          $b['created_at'] = $k->created_at->format('d/M/Y h:m:s');
                  
          array_push($items, $b);
        }
        return response()->json($items);
      }else {
        $items = array();
        array_push($items);
        return response()->json($items);
      }
  }

  // Satu artikel by id untuk halaman detail web (mobile mengirim konten
  // penuh via query params — web butuh endpoint sendiri). created_at
  // dikirim mentah Y-m-d H:i:s karena format 'd/M/Y h:m:s' di blog()/
  // blogbyfilter() rusak (m = nama bulan, bukan menit).
  public function blogbyid(Request $request){
      $k = Blog::join('member','blog.idmember','=','member.id')
        ->where('blog.id',$request->id)
        ->first(['blog.*','member.name as nama']);
      if (!$k) {
          return response()->json(null, 404);
      }
      return response()->json([
        'id' => $k->id,
        'name' => $k->name,
        'deskripsi' => $k->deskripsi,
        'viewer' => $k->viewer,
        'cover' => $k->cover ? $request->getSchemeAndHttpHost() . '/assets/' . $k->cover : '',
        'kategori' => $k->kategori,
        'nama' => $k->nama,
        'created_at' => $k->created_at ? $k->created_at->format('Y-m-d H:i:s') : null,
      ]);
  }

  // ===== CRUD blog untuk admin web (mobile tidak punya kelola blog) =====

  public function uploadcover(Request $request){
    if (!$request->hasFile('image')) {
      return response()->json(['value' => '400', 'pesan' => 'File image wajib diisi'], 400);
    }
    $file = $request->file('image');
    $ext = strtolower($file->getClientOriginalExtension());
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
      return response()->json(['value' => '400', 'pesan' => 'Ekstensi harus jpg/jpeg/png/webp'], 400);
    }
    if ($file->getSize() > 2 * 1024 * 1024) {
      return response()->json(['value' => '400', 'pesan' => 'Ukuran maksimal 2MB'], 400);
    }
    // Cover blog diserved dari public/assets/{cover} — simpan filename polos
    // agar blog()/blogbyid() lama tetap bisa mem-prefix /assets/.
    $destinationPath = base_path('public/assets');
    if (!file_exists($destinationPath)) {
      mkdir($destinationPath, 0775, true);
    }
    $name = 'cover_' . time() . '_' . substr(md5($file->getClientOriginalName() . microtime()), 0, 6) . '.' . $ext;
    $file->move($destinationPath, $name);
    return response()->json(['value' => '200', 'pesan' => 'Success', 'cover' => $name]);
  }

  public function tambahblog(Request $request){
    $name = trim((string)$request->input('name'));
    $deskripsi = trim((string)$request->input('deskripsi'));
    $kategori = trim((string)$request->input('kategori'));
    $idmember = (int)$request->input('idmember');
    $cover = trim((string)$request->input('cover'));
    if ($name === '' || $deskripsi === '' || $kategori === '') {
      return response()->json(['value' => '400', 'pesan' => 'Judul, deskripsi, dan kategori wajib diisi'], 400);
    }
    if (!in_array($kategori, ['artikel', 'berita'])) {
      return response()->json(['value' => '400', 'pesan' => 'Kategori harus artikel atau berita'], 400);
    }
    if (!Member::where('id', $idmember)->exists()) {
      return response()->json(['value' => '404', 'pesan' => 'Penulis tidak ditemukan'], 404);
    }
    $b = new Blog();
    $b->name = $name;
    $b->deskripsi = $deskripsi;
    $b->kategori = $kategori;
    $b->idmember = $idmember;
    $b->viewer = 0;
    $b->cover = $cover !== '' ? $cover : null;
    $b->save();
    return response()->json(['value' => '200', 'pesan' => 'Success', 'id' => $b->id]);
  }

  public function editblog(Request $request){
    $b = Blog::find($request->id);
    if (!$b) {
      return response()->json(['value' => '404', 'pesan' => 'Artikel tidak ditemukan'], 404);
    }
    if ($request->filled('name')) $b->name = trim((string)$request->input('name'));
    if ($request->filled('deskripsi')) $b->deskripsi = trim((string)$request->input('deskripsi'));
    if ($request->filled('kategori')) {
      $kategori = trim((string)$request->input('kategori'));
      if (!in_array($kategori, ['artikel', 'berita'])) {
        return response()->json(['value' => '400', 'pesan' => 'Kategori harus artikel atau berita'], 400);
      }
      $b->kategori = $kategori;
    }
    if ($request->filled('cover')) $b->cover = trim((string)$request->input('cover'));
    $b->save();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }

  public function hapusblog(Request $request){
    $b = Blog::find($request->id);
    if (!$b) {
      return response()->json(['value' => '404', 'pesan' => 'Artikel tidak ditemukan'], 404);
    }
    $b->delete();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }

  // ===== CRUD slider untuk admin web (satu slider untuk web & mobile) =====

  public function tambahslider(Request $request){
    $judul = trim((string)$request->input('judul'));
    $deskripsi = trim((string)$request->input('deskripsi'));
    $gambar = trim((string)$request->input('gambar'));
    if ($judul === '' || $deskripsi === '' || $gambar === '') {
      return response()->json(['value' => '400', 'pesan' => 'Judul, deskripsi, dan gambar wajib diisi'], 400);
    }
    $s = new Slider();
    $s->judul = $judul;
    $s->deskripsi = $deskripsi;
    $s->gambar = $gambar; // filename polos di public/assets (pola sama dgn blog)
    $s->save();
    return response()->json(['value' => '200', 'pesan' => 'Success', 'id' => $s->id]);
  }

  public function editslider(Request $request){
    $s = Slider::find($request->id);
    if (!$s) {
      return response()->json(['value' => '404', 'pesan' => 'Slider tidak ditemukan'], 404);
    }
    if ($request->filled('judul')) $s->judul = trim((string)$request->input('judul'));
    if ($request->filled('deskripsi')) $s->deskripsi = trim((string)$request->input('deskripsi'));
    if ($request->filled('gambar')) $s->gambar = trim((string)$request->input('gambar'));
    $s->save();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }

  public function hapusslider(Request $request){
    $s = Slider::find($request->id);
    if (!$s) {
      return response()->json(['value' => '404', 'pesan' => 'Slider tidak ditemukan'], 404);
    }
    $s->delete();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }

  // ===== Edit/hapus pohon untuk admin web =====

  // Upload foto utama pohon (param file "foto" di editpohon/tambahpohon):
  // simpan ke public/upload/pohon, balas [url|null, errorResponse|null].
  // URL dibangun dari origin request sehingga selalu menunjuk server API.
  private function prosesFotoPohon(Request $request){
    if (!$request->hasFile('foto')) return [null, null];
    $file = $request->file('foto');
    $ext = strtolower($file->getClientOriginalExtension());
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
      return [null, response()->json(['value' => '400', 'pesan' => 'Ekstensi foto harus jpg/jpeg/png/webp'], 400)];
    }
    if ($file->getSize() > 2 * 1024 * 1024) {
      return [null, response()->json(['value' => '400', 'pesan' => 'Ukuran foto maksimal 2MB'], 400)];
    }
    $kode = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$request->input('idpohon'));
    $name_file = 'pohon_'.$kode.'_'.time().'_'.strtoupper(substr(md5(uniqid(rand(), true)), 0, 4)).'.'.$ext;
    $destinationPath = base_path('public/upload/pohon');
    if (!file_exists($destinationPath)) {
      mkdir($destinationPath, 0775, true);
    }
    $file->move($destinationPath, $name_file);
    $url = $request->getSchemeAndHttpHost().str_replace('/index.php', '', $request->getBaseUrl()).'/upload/pohon/'.$name_file;
    return [$url, null];
  }

  public function editpohon(Request $request){
    $idpohon = trim((string)$request->input('idpohon'));
    $p = Pohon::where('idpohon', $idpohon)->first();
    if (!$p) {
      return response()->json(['value' => '404', 'pesan' => 'Pohon tidak ditemukan'], 404);
    }
    if ($request->filled('localname')) $p->localname = trim((string)$request->input('localname'));
    if ($request->filled('species')) $p->species = trim((string)$request->input('species'));
    if ($request->filled('family')) $p->family = trim((string)$request->input('family'));
    if ($request->filled('desa')) {
      $desaNama = trim((string)$request->input('desa'));
      if (!Desa::where('nama', $desaNama)->exists()) {
        return response()->json(['value' => '404', 'pesan' => 'Desa tidak ditemukan'], 404);
      }
      $p->desa = $desaNama;
    }
    if ($request->filled('diameter')) $p->diameter = (int)$request->input('diameter');
    if ($request->filled('tinggi')) $p->tinggi = (int)$request->input('tinggi');
    if ($request->filled('harga')) {
      $harga = (int)$request->input('harga');
      if ($harga <= 0) {
        return response()->json(['value' => '400', 'pesan' => 'Harga harus lebih dari 0'], 400);
      }
      $p->harga = $harga;
      $p->price = $harga;
    }
    [$fotoUrl, $fotoErr] = $this->prosesFotoPohon($request);
    if ($fotoErr) return $fotoErr;
    if ($fotoUrl !== null) $p->foto_pohon = $fotoUrl;
    elseif ($request->filled('foto_pohon')) $p->foto_pohon = trim((string)$request->input('foto_pohon'));
    $p->save();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }

  public function hapuspohon(Request $request){
    $idpohon = trim((string)$request->input('idpohon'));
    $p = Pohon::where('idpohon', $idpohon)->first();
    if (!$p) {
      return response()->json(['value' => '404', 'pesan' => 'Pohon tidak ditemukan'], 404);
    }
    if ($p->adopted !== 'available') {
      return response()->json(['value' => '409', 'pesan' => 'Pohon sudah diadopsi/dipesan, tidak bisa dihapus'], 409);
    }
    if (Dataadopsi::where('idpohon', $idpohon)->exists()) {
      return response()->json(['value' => '409', 'pesan' => 'Pohon punya riwayat order, tidak bisa dihapus'], 409);
    }
    $p->delete();
    return response()->json(['value' => '200', 'pesan' => 'Success']);
  }

  public function register (Request $request){
    $data=new Member();
    $cekemail= Member::where('emaile',$request->emaile)->first();
    if($cekemail){
      return response()->json(
        [
          'message'=> 'Email Existing',
          'value'=>0 
        ]
        );
    }else{
      $tanggal=Date('Y-m-d');
      $data->tanggal=$tanggal;
      $data->name=$request->input('name');
      $data->emaile=$request->input('emaile');
      $data->hp=$request->input('hp');
      //$data->address=$request->input('address');
     // $data->passe=Hash::make($request->input('passe'));
      $data->passe=md5(sha1($request->input('passe')) );
      $data->count=0;
      $data->admin=0;
      $data->confirmed="no";
      $data->tampil="yes";
      $data->status="Member";
      //$data->website=$request->input('website');
      $data->mati=0;
      $data->coba=0;
      $data->kode='';
    
      $data->foto='';
      $data->tentang='';
      $data->job='';
      $data->id_token=$request->id_token;
      $data->save();
      return response()->json(
        [
          'message'=> 'Registration Success',
          'value'=>1
        ]
        );
      
    }


  }



  public function getketegori_service (){
      $data= Kategori_service::all();
      if(count($data)>0){
        $items = array();
        foreach ($data as $k) {
          $b['id'] = $k->id;
          $b['name'] = $k->name;
                 
  
          array_push($items, $b);
        }
        return response()->json($items);
      }else {
        $items = array();
        array_push($items);
        return response()->json($items);
      }

  }



  public function loginuser(Request $request){
    Log::info(request()->all());
    $email=$request->input('emaile');
    $password=$request->input('passe');
    $id_token=$request->input('id_token');
    $buatpassword =md5( sha1($request->input('passe')) );


    $ceklogin=Member::where('emaile',$email)
    ->orwhere('hp',$email)
    ->first();

    // Member yang dinonaktifkan admin tidak boleh masuk (web & mobile).
    if ($ceklogin && (int)($ceklogin->aktif ?? 1) === 0) {
        return response()->json([
            'message' => 'Akun dinonaktifkan',
            'value' => '403',
        ], 403);
    }


    
    

    if(is_numeric($email)){
     
                      if($ceklogin){
                    
                  
          if($buatpassword != $ceklogin->passe){
                            return response()->json(
                          [
                            'message'=> 'User Name & Password not Matchs',
                            
                            'value'=>"205",
                              
                          ]
                          );
                        }else{
                             $ceklogin->id_token=$request->id_token;
                      $ceklogin->update();

                      return response()->json([
                      'value' => "200",
                      'email' => $ceklogin->emaile,
                      'name' => $ceklogin->name,
                      'id' => $ceklogin->id,
                      'admin' => $ceklogin->admin,
                    ]);
                        }
                      /*
                      if(!$ceklogin || !Hash::check($password, $ceklogin->passe)){
                          return response()->json(
                          [
                            'message'=> 'User Name & Password Not Matchs',
                            'value'=>"205",
                              
                          ]
                          );
                          
                      } else {

                        $ceklogin->id_token=$request->id_token;
                        $ceklogin->update();
                        return response()->json([
                        'value' => "200",
                        'email' => $ceklogin->emaile,
                        'name' => $ceklogin->name,
                        'id' => $ceklogin->id,
                        'admin' => $ceklogin->admin,
                      ]);
                      }
                      
                      */

                  }else{
                    return response()->json(
                      [
                        'message'=> 'Email Member Not Found',
                        'value'=>"0" 
                      ]
                      );
                  }

    }else{
                  
                    
                    if($ceklogin){
                     
                           if($buatpassword != $ceklogin->passe){
                            return response()->json(
                          [
                            'message'=> 'User Name & Password not Matchs',
                            
                            'value'=>"205",
                              
                          ]
                          );
                        }else{
                              $ceklogin->id_token=$request->id_token;
                      $ceklogin->update();

                      return response()->json([
                      'value' => "200",
                      'email' => $ceklogin->emaile,
                      'name' => $ceklogin->name,
                      'id' => $ceklogin->id,
                      'admin' => $ceklogin->admin,
                    ]);
                        }
                     
                     
                     /*
                    if(!$ceklogin || !Hash::check($password, $ceklogin->passe)){
                        return response()->json(
                        [
                          'message'=> 'User Name & Password Not Matchs',
                          'value'=>"205",
                            
                        ]
                        );
                    } else {

                      $ceklogin->id_token=$request->id_token;
                      $ceklogin->update();

                      return response()->json([
                      'value' => "200",
                      'email' => $ceklogin->emaile,
                      'name' => $ceklogin->name,
                      'id' => $ceklogin->id,
                      'admin' => $ceklogin->admin,
                    ]);
                    }
                    */

                }else{
                  return response()->json(
                    [
                      'message'=> 'Email Member Not Found',
                      'value'=>"0" 
                    ]
                    );
                }


    }

    
    
  }


  public function updatepelanggan($id, Request $request){
    
    $data=Pelanggan::where('id',$id)->first();
    $data->nama=$request->nama;
    $data->alamat=$request->alamat;
    $data->update();
    return response()->json(
      [
        'message'=> 'Update Success',
        'code'=>200 
      ]
      );

  }
    

  public function editpelanggan($id){
    $data=Pelanggan::find($id);
    return response()->json($data);

  }

  public function deletepelanggan($id){
    $data=Pelanggan::find($id);
    if($data){
      $data->delete();
      return response()->json(
        [
          'message'=> 'Delete Success',
          'code'=>200 
        ]
        );
    }else{
      return response()->json(
        [
          'message'=> "Data dengan ID = $id Tidak Ditemukan",
          'code'=>404
        ]
        );
    }

  }

  public function savepelanggan(Request $request){

    $data=new Pelanggan();
    $data->nama=$request->nama;
    $data->alamat=$request->alamat;
    $data->save();
    return response()->json(
      [
         
          'message'=> 'Save Success',
          'code'=>200
      ]
      );
  }

    public function pelanggan(){
        $data = Pelanggan::All();
        return response()->json(
            [
                'pelangganku'=>$data,
                'message'=> 'Pelanggan',
                'code'=>200
            ]
            );
  
    }



 
    // ===== Fitur adopsi lindungihutan.com: statistik dampak, katalog
    // spesies + karbon, testimoni & partner (beranda web) =====

    // Angka dampak utk section statistik beranda web.
    public function statistikdampak(Request $request){
        $p = Pohon::selectRaw("COUNT(*) AS j, SUM(adopted='adopted') AS a, SUM(adopted='available') AS av, COUNT(DISTINCT desa) AS d")->first();
        return response()->json([
            'pohon' => (int)$p->j,
            'diadopsi' => (int)$p->a,
            'tersedia' => (int)$p->av,
            'desa' => (int)$p->d,
            'donatur' => (int)Member::count(),
        ]);
    }

    // Daftar spesies katalog (A-Z) + jumlah pohon terdata per spesies.
    // species di data_pohon berantakan → dicocokkan via species_key
    // (variasi nama dipisah koma) terhadap TRIM(species) dalam SATU
    // query agregat.
    public function specieslist(Request $request){
        $rows = SpeciesCatalog::orderBy('nama_latin')->get();
        $perKey = Pohon::selectRaw("TRIM(species) AS s, COUNT(*) AS j")
            ->whereNotNull('species')->where('species', '!=', '')
            ->groupBy('s')->get()->keyBy('s');
        $items = [];
        foreach ($rows as $r) {
            $jml = 0;
            foreach (array_map('trim', array_filter(explode(',', (string)$r->species_key))) as $key) {
                $jml += (int)($perKey[$key]->j ?? 0);
            }
            $items[] = [
                'id' => $r->id,
                'nama_latin' => $r->nama_latin,
                'nama_lokal' => (string)$r->nama_lokal,
                'famili' => (string)$r->famili,
                'deskripsi' => (string)$r->deskripsi,
                'serapan_karbon' => $r->serapan_karbon !== null ? (float)$r->serapan_karbon : null,
                'foto' => $r->foto && str_starts_with($r->foto, 'http') ? $r->foto : ($r->foto ? $request->getSchemeAndHttpHost() . '/assets/' . $r->foto : ''),
                'jml_pohon' => $jml,
            ];
        }
        return response()->json($items);
    }

    // Detail spesies + pohon available & breakdown desa terkait.
    public function speciesdetail(Request $request, $id){
        $r = SpeciesCatalog::find($id);
        if (!$r) {
            return response()->json(null, 404);
        }
        $keys = array_values(array_map('trim', array_filter(explode(',', (string)$r->species_key))));
        $base = function () use ($keys) {
            $q = Pohon::query();
            if ($keys) {
                $q->whereRaw("TRIM(species) IN (" . implode(',', array_fill(0, count($keys), '?')) . ")", $keys);
            } else {
                $q->whereRaw('0 = 1'); // tanpa key → tak ada pohon terkait
            }
            return $q;
        };
        $totalTerkait = $base()->count();
        $pohonTerkait = $base()->where('adopted', 'available')->orderBy('idpohon')->limit(12)->get();
        $desaRows = $base()->selectRaw("desa, COUNT(*) AS j")->groupBy('desa')->get();

        $pohon = [];
        foreach ($pohonTerkait as $p) {
            $pohon[] = [
                'idpohon' => $p->idpohon,
                'localname' => $p->localname,
                'species' => $p->species,
                'desa' => $p->desa,
                'harga' => $p->harga,
                'foto_pohon' => $p->foto_pohon,
            ];
        }
        $desa = [];
        foreach ($desaRows as $d) {
            $desa[] = ['nama' => $d->desa, 'jml' => (int)$d->j];
        }

        return response()->json([
            'id' => $r->id,
            'nama_latin' => $r->nama_latin,
            'nama_lokal' => (string)$r->nama_lokal,
            'famili' => (string)$r->famili,
            'deskripsi' => (string)$r->deskripsi,
            'serapan_karbon' => $r->serapan_karbon !== null ? (float)$r->serapan_karbon : null,
            'foto' => $r->foto && str_starts_with($r->foto, 'http') ? $r->foto : ($r->foto ? $request->getSchemeAndHttpHost() . '/assets/' . $r->foto : ''),
            'species_key' => (string)$r->species_key,
            'jml_pohon' => $totalTerkait,
            'pohon' => $pohon,
            'desa' => $desa,
        ]);
    }

    // ===== CRUD spesies (admin web) =====

    public function tambahspecies(Request $request){
        $namaLatin = trim((string)$request->input('nama_latin'));
        if ($namaLatin === '') {
            return response()->json(['value' => '400', 'pesan' => 'Nama latin wajib diisi'], 400);
        }
        if (SpeciesCatalog::where('nama_latin', $namaLatin)->exists()) {
            return response()->json(['value' => '400', 'pesan' => 'Nama latin sudah ada di katalog'], 400);
        }
        $s = new SpeciesCatalog();
        $s->nama_latin = $namaLatin;
        $s->nama_lokal = trim((string)$request->input('nama_lokal')) ?: null;
        $s->famili = trim((string)$request->input('famili')) ?: null;
        $s->deskripsi = trim((string)$request->input('deskripsi')) ?: null;
        $karbon = str_replace(',', '.', trim((string)$request->input('serapan_karbon')));
        $s->serapan_karbon = $karbon !== '' ? (is_numeric($karbon) ? $karbon : null) : null;
        $s->foto = trim((string)$request->input('foto')) ?: null;
        $s->species_key = trim((string)$request->input('species_key')) ?: null;
        $s->save();
        return response()->json(['value' => '200', 'pesan' => 'Success', 'id' => $s->id]);
    }

    public function editspecies(Request $request){
        $s = SpeciesCatalog::find($request->id);
        if (!$s) {
            return response()->json(['value' => '404', 'pesan' => 'Spesies tidak ditemukan'], 404);
        }
        if ($request->filled('nama_latin')) {
            $namaLatin = trim((string)$request->input('nama_latin'));
            if ($namaLatin === '') {
                return response()->json(['value' => '400', 'pesan' => 'Nama latin wajib diisi'], 400);
            }
            $s->nama_latin = $namaLatin;
        }
        if ($request->filled('nama_lokal')) $s->nama_lokal = trim((string)$request->input('nama_lokal'));
        if ($request->filled('famili')) $s->famili = trim((string)$request->input('famili'));
        if ($request->filled('deskripsi')) $s->deskripsi = trim((string)$request->input('deskripsi'));
        if ($request->filled('serapan_karbon')) {
            $karbon = str_replace(',', '.', trim((string)$request->input('serapan_karbon')));
            $s->serapan_karbon = is_numeric($karbon) ? $karbon : null;
        }
        if ($request->filled('foto')) $s->foto = trim((string)$request->input('foto'));
        if ($request->filled('species_key')) $s->species_key = trim((string)$request->input('species_key'));
        $s->save();
        return response()->json(['value' => '200', 'pesan' => 'Success']);
    }

    public function hapusspecies(Request $request){
        $s = SpeciesCatalog::find($request->id);
        if (!$s) {
            return response()->json(['value' => '404', 'pesan' => 'Spesies tidak ditemukan'], 404);
        }
        $s->delete();
        return response()->json(['value' => '200', 'pesan' => 'Success']);
    }

    // ===== Testimoni & partner beranda (admin web) =====

    public function testimonilist(){
        return response()->json(Testimoni::orderBy('urutan')->orderByDesc('id')->get());
    }

    public function tambahtestimoni(Request $request){
        $nama = trim((string)$request->input('nama'));
        $isi = trim((string)$request->input('isi'));
        if ($nama === '' || $isi === '') {
            return response()->json(['value' => '400', 'pesan' => 'Nama dan isi testimoni wajib diisi'], 400);
        }
        $t = new Testimoni();
        $t->nama = $nama;
        $t->peran = trim((string)$request->input('peran')) ?: null;
        $t->isi = $isi;
        $t->urutan = (int)($request->input('urutan') ?? 0);
        $t->save();
        return response()->json(['value' => '200', 'pesan' => 'Success', 'id' => $t->id]);
    }

    public function edittestimoni(Request $request){
        $t = Testimoni::find($request->id);
        if (!$t) {
            return response()->json(['value' => '404', 'pesan' => 'Testimoni tidak ditemukan'], 404);
        }
        if ($request->filled('nama')) $t->nama = trim((string)$request->input('nama'));
        if ($request->filled('peran')) $t->peran = trim((string)$request->input('peran'));
        if ($request->filled('isi')) $t->isi = trim((string)$request->input('isi'));
        if ($request->filled('urutan')) $t->urutan = (int)$request->input('urutan');
        $t->save();
        return response()->json(['value' => '200', 'pesan' => 'Success']);
    }

    public function hapustestimoni(Request $request){
        $t = Testimoni::find($request->id);
        if (!$t) {
            return response()->json(['value' => '404', 'pesan' => 'Testimoni tidak ditemukan'], 404);
        }
        $t->delete();
        return response()->json(['value' => '200', 'pesan' => 'Success']);
    }

    public function partnerlist(Request $request){
        $items = [];
        foreach (Partner::orderBy('urutan')->orderByDesc('id')->get() as $k) {
            $items[] = [
                'id' => $k->id,
                'nama' => $k->nama,
                'logo' => $k->logo ? $request->getSchemeAndHttpHost() . '/assets/' . $k->logo : '',
                'url' => (string)$k->url,
                'urutan' => (int)$k->urutan,
            ];
        }
        return response()->json($items);
    }

    public function tambahpartner(Request $request){
        $nama = trim((string)$request->input('nama'));
        if ($nama === '') {
            return response()->json(['value' => '400', 'pesan' => 'Nama partner wajib diisi'], 400);
        }
        $p = new Partner();
        $p->nama = $nama;
        $p->logo = trim((string)$request->input('logo')) ?: null;
        $p->url = trim((string)$request->input('url')) ?: null;
        $p->urutan = (int)($request->input('urutan') ?? 0);
        $p->save();
        return response()->json(['value' => '200', 'pesan' => 'Success', 'id' => $p->id]);
    }

    public function editpartner(Request $request){
        $p = Partner::find($request->id);
        if (!$p) {
            return response()->json(['value' => '404', 'pesan' => 'Partner tidak ditemukan'], 404);
        }
        if ($request->filled('nama')) $p->nama = trim((string)$request->input('nama'));
        if ($request->filled('logo')) $p->logo = trim((string)$request->input('logo'));
        if ($request->filled('url')) $p->url = trim((string)$request->input('url'));
        if ($request->filled('urutan')) $p->urutan = (int)$request->input('urutan');
        $p->save();
        return response()->json(['value' => '200', 'pesan' => 'Success']);
    }

    public function hapuspartner(Request $request){
        $p = Partner::find($request->id);
        if (!$p) {
            return response()->json(['value' => '404', 'pesan' => 'Partner tidak ditemukan'], 404);
        }
        $p->delete();
        return response()->json(['value' => '200', 'pesan' => 'Success']);
    }

    public function getUser()
        {

        	$data=User::select("*")
        	->orderBy("created_at")
         ->get();
         
          if (count($data) > 0) {
            $items = array();
            foreach ($data as $item) {
              $b['name'] = $item->name;
            
              array_push($items, $b);
            }
            return response()->json($items);
          } else {
            $items = array();
            array_push($items);
            return response()->json($items);
          }
        }
}

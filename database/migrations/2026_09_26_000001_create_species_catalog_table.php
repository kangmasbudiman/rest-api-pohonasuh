<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Ensiklopedia spesies untuk katalog web (kolom species di data_pohon
// berantakan — typo/spasi/"sp" — jadi katalog dikurasi terpisah dan
// dihubungkan ke pohon lewat species_key: variasi nama di data_pohon
// dipisah koma, dicocokkan dengan TRIM(species)).
class CreateSpeciesCatalogTable extends Migration
{
    public function up()
    {
        Schema::create('species_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('nama_latin', 150)->unique();
            $table->string('nama_lokal', 150)->nullable();
            $table->string('famili', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('serapan_karbon', 8, 2)->nullable(); // kg CO2/pohon/tahun (estimasi)
            $table->string('foto')->nullable(); // filename di public/assets ATAU URL absolut
            $table->string('species_key', 255)->nullable();
            $table->timestamps();
        });

        // Seed 12 spesies utama (jml pohon & key diverifikasi dari data_pohon
        // per 2026-09-26; serapan karbon = estimasi literatur, editable admin).
        DB::table('species_catalog')->insert([
            [
                'nama_latin' => 'Rhodoleia championii', 'nama_lokal' => 'Kasiah Baranak',
                'famili' => 'Hammamelidaceae', 'serapan_karbon' => 25,
                'species_key' => 'Rhodoleia championii',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/simarasok/SM011_1.jpg',
                'deskripsi' => 'Pohon hutan dataran rendah yang bunganya merah menyala tersusun majemuk, menjadi sumber nektar penting bagi burung dan serangga penyerbuk. Kayunya tergolong ringan dan umum dimanfaatkan masyarakat untuk perkakas. Di Jambi, spesies ini menjadi salah satu jenis paling melimpah dalam program Pohon Asuh.',
            ],
            [
                'nama_latin' => 'Litsea complex', 'nama_lokal' => 'Medang',
                'famili' => 'Lauraceae', 'serapan_karbon' => 18,
                'species_key' => 'Litsea SP,Litsea grandis',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/airtenam/ATM0201_1.jpg',
                'deskripsi' => 'Kelompok medang (Litsea spp.) mencakup banyak jenis hutan sekunder maupun primer dengan daun aromatik khas keluarga laurel. Kayunya luruh dan ringan, dipakai untuk perkakas rumah tangga dan gagang alat. Medang tumbuh cepat sehingga berperan penting memulihkan tutupan hutan muda.',
            ],
            [
                'nama_latin' => 'Lithocarpus palembanicus', 'nama_lokal' => 'Kenolan',
                'famili' => 'Fagaceae', 'serapan_karbon' => 30,
                'species_key' => 'Lithocarpus palembanica',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/rantaukermas/A001_1.jpg',
                'deskripsi' => 'Anggota keluarga pasang-pasangan (Fagaceae) hutan dataran rendah Sumatra. Buahnya seperti biji pasang keras yang menjadi makanan satwa hutan, sementara kayunya kuat dan awet untuk konstruksi. Populasinya sangat melimpah di hutan desa Rantau Kremas.',
            ],
            [
                'nama_latin' => 'Shorea parvifolia', 'nama_lokal' => 'Meranti Putih',
                'famili' => 'Dipterocarpaceae', 'serapan_karbon' => 35,
                'species_key' => 'Shorea parvifolia',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/laham/LHM001_1.jpg',
                'deskripsi' => 'Meranti putih adalah dipterokarp cepat tumbuh yang menjadi andalan komunitas hutan produksi alam Sumatra. Kayunya ringan hingga sedang, mudah dikerjakan, dan dipakai untuk perlengkapan bangunan serta mebel. Pohon dewasanya menjulang membentuk tajuk kanopi hutan.',
            ],
            [
                'nama_latin' => 'Syzygium complex', 'nama_lokal' => 'Gelam / Kalek',
                'famili' => 'Myrtaceae', 'serapan_karbon' => 20,
                'species_key' => 'Syzygium sp',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/baturajar/LN004_1.jpg',
                'deskripsi' => 'Kelompok gelam/kalek (Syzygium spp.) dari keluarga jambu-jambuan tersebar hampir di semua lokasi program. Beberapa jenisnya menghasilkan buah yang dimanfaatkan satwa dan penduduk, sementara kayunya cukup kuat untuk konstruksi ringan. Daun mudanya kerap merah menarik sebelum menghijau.',
            ],
            [
                'nama_latin' => 'Koompassia malaccensis', 'nama_lokal' => 'Kempas',
                'famili' => 'Fabaceae', 'serapan_karbon' => 40,
                'species_key' => 'Kompassia sumatrana',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/sinarwajo/SWA0025_1.jpg',
                'deskripsi' => 'Kempas adalah salah satu kayu keras komersial terpenting Sumatra — kuat, awet, dan tali akarnya khas kawasan basah. Pohonnya berukuran besar dengan batang lurus menjulang; bunganya menarik lebah madu hutan. Populasi besar kempas terdata di hutan desa Sinar Wajo.',
            ],
            [
                'nama_latin' => 'Palaquium sumatranum', 'nama_lokal' => 'Balam',
                'famili' => 'Sapotaceae', 'serapan_karbon' => 28,
                'species_key' => 'Palaquium sumatrana',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/pondokparianlunang/PAPL011_1.jpg',
                'deskripsi' => 'Balam menghasilkan getah gutta-percak yang dahulu bernilai ekonomi tinggi, kini lebih dikenal lewat kayunya yang kuat untuk bantalan dan konstruksi berat. Buahnya manis dimakan satwa sehingga pohon ini menjadi penopang ekologi hutan dataran rendah.',
            ],
            [
                'nama_latin' => 'Shorea leprosula', 'nama_lokal' => 'Meranti Bunga',
                'famili' => 'Dipterocarpaceae', 'serapan_karbon' => 35,
                'species_key' => 'Shorea leprosula',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/guguk/GG001A_1.jpg',
                'deskripsi' => 'Meranti bunga tumbuh cepat dengan kulit batang mengelupas halus dan tajuk lebat — salah satu penyerap karbon terbaik di antara dipterokarp. Kayunya bernilai tinggi untuk kayu lapis dan mebel. Jenis ini tersebar di banyak desa program Pohon Asuh.',
            ],
            [
                'nama_latin' => 'Dryobalanops lanceolata', 'nama_lokal' => 'Kapur / Ngai',
                'famili' => 'Dipterocarpaceae', 'serapan_karbon' => 35,
                'species_key' => 'Dryobalanops lanceolata',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/longlake/LL066_1.jpg',
                'deskripsi' => 'Kapur (ngai) terkenal lewat kapur barus yang dahulu diekspor Nusantara ke dunia. Kayunya beraroma kamper dan sangat awet, digolongkan kayu konstruksi kelas berat. Populasi besar terdata di hutan desa Long Lake.',
            ],
            [
                'nama_latin' => 'Gluta renghas', 'nama_lokal' => 'Rengas',
                'famili' => 'Anacardiaceae', 'serapan_karbon' => 30,
                'species_key' => 'Glutta renghas',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/sinarwajo/SWA0065_1.jpg',
                'deskripsi' => 'Rengas berkerabat dengan jambu monyet dan menghasilkan getah keras yang menyebabkan gatal bila tersentuh — namun kayunya yang merah kehitaman sangat indah untuk mebel dan ukiran. Di hutan, rengas tumbuh tegak menjulang bersama jenis meranti.',
            ],
            [
                'nama_latin' => 'Durio zibethinus', 'nama_lokal' => 'Durian',
                'famili' => 'Bombacaceae', 'serapan_karbon' => 15,
                'species_key' => 'Durio zibethinus',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/longlake/LL006_1.jpg',
                'deskripsi' => 'Durian hutan adalah pohon buah ikonik Sumatra: buahnya dimanfaatkan masyarakat dan satwa, kayu bekas buahnya dipakai untuk perkakas dan konstruksi ringan. Pohon durian liar dalam program ini tumbuh alami di dalam hutan desa, bukan kebun.',
            ],
            [
                'nama_latin' => 'Artocarpus elasticus', 'nama_lokal' => 'Tarok / Teureup',
                'famili' => 'Moraceae', 'serapan_karbon' => 22,
                'species_key' => 'Artocarpus elasticus',
                'foto' => 'https://pohonasuh.org/assets/images/data/foto/KBKA/KBKA002_1.jpg',
                'deskripsi' => 'Teureup berkerabat dengan sukun dan nangka; buah dan bijinya dapat dimakan sesudah diolah, serat kulit batangnya dahulu dijadikan pakaian. Sebagai pohon pionir cepat tumbuh, teureup membantu mempercepat pemulihan hutan yang terbuka.',
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('species_catalog');
    }
}

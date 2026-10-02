@extends('layouts.dashboard')
@section('isi')
    @if($shift_karyawan->count() > 0)
        @foreach ($shift_karyawan as $sk)
            <?php $skid = $sk->id ?>
            <?php $sktanggal = $sk->tanggal ?>
            <?php $sknamas = $sk->Shift->nama_shift  ?>
            <?php $skjamas = $sk->Shift->jam_masuk ?>
            <?php $skjamkel = $sk->Shift->jam_keluar ?>
            <?php $skjamab = $sk->jam_absen ?>
            <?php $skjampul = $sk->jam_pulang ?>
            <?php $skstatus = $sk->status_absen ?>

        @endforeach
    @else
        <?php $skid = "-" ?>
        <?php $sktanggal = "-" ?>
        <?php $sknamas = "-"  ?>
        <?php $skjamas = "-" ?>
        <?php $skjamkel = "-" ?>
        <?php $skjamab = "-" ?>
        <?php $skjampul = "-" ?>
        <?php $skstatus = "-" ?>
    @endif
    <div class="container-fluid">
        <center style="color: white">
            <p class="p mb-2 text-gray-800">Tanggal Shift : {{ $sktanggal }}</p>
            <p class="p mb-2 text-gray-800">Shift : {{ $sknamas}} ({{ $skjamas }} - {{  $skjamkel }})</p>
        </center>

        <style>
            h1,
            h2,
            p,
            a {
              font-family: sans-serif;
              font-weight: 8;
            }

            .jam-digital-malasngoding {
              overflow: hidden;
              float: center;
              width: 100px;
              margin: 2px auto;
              border: 0px solid #efefef;
            }

            .kotak {
              float: left;
              width: 30px;
              height: 30px;
              background-color: #189fff;
            }

            .jam-digital-malasngoding p {
              color: #fff;
              font-size: 16px;
              text-align: center;
              margin-top: 3px;
            }
        </style>

        <div class="jam-digital-malasngoding">
            <div class="kotak">
              <p id="jam"></p>
            </div>
            <div class="kotak">
              <p id="menit"></p>
            </div>
            <div class="kotak">
              <p id="detik"></p>
            </div>
        </div>

        <script>
            window.setTimeout("waktu()", 1000);

            function waktu() {
              var waktu = new Date();
              setTimeout("waktu()", 1000);
              document.getElementById("jam").innerHTML = waktu.getHours();
              document.getElementById("menit").innerHTML = waktu.getMinutes();
              document.getElementById("detik").innerHTML = waktu.getSeconds();
            }
        </script>
        <br>

        <div class="d-flex justify-content-center">
            <form action="{{ url('/my-location') }}" method="get">
                @csrf
                <input type="hidden" name="lat" id="lat2">
                <input type="hidden" name="long" id="long2">
                <input type="hidden" name="userid" value="{{ auth()->user()->id }}">
                <button type="submit" class="btn btn-success">Lihat Lokasi Saya</button>
            </form>
        </div>

        @if($shift_karyawan->count() == 0)
        <br>
        <div class="card col-lg-12">
        <div class="mt-5">
            <div class="mb-5">
                <center>
                    <h2>Hubungi Admin Untuk Mapping Shift Anda</h2>
                </center>
            </div>
        </div>
        </div>
        @elseif($skstatus == "Libur")
        <br>
        <div class="card col-lg-12">
        <div class="mt-5">
            <div class="mb-5">
                <center>
                    <h2>Hari Ini Anda Libur</h2>
                </center>
            </div>
        </div>
        </div>
        @elseif($skstatus == "Cuti")
        <br>
        <div class="card col-lg-12">
        <div class="mt-5">
            <div class="mb-5">
            <center>
                <h2>Hari Ini Anda Cuti</h2>
            </center>
            </div>
        </div>
        </div>
        @else
            @if($skjamab == null)
            <br>
                <div class="card col-lg-12">
                    <div class="mt-4">
                        <form method="post" action="{{ url('/absen/masuk/'.$skid) }}">
                            @method('put')
                            @csrf
                            <div class="form-row">
                                <div class="col"></div>
                                <div class="col">
                                    <center>
                                        <h2>Absen Masuk: </h2>
                                        <div class="webcam" id="results"></div>
                                    </center>
                                </div>
                                <div class="col">
                                    <input type="hidden" name="jam_absen" value="{{ date('H:i') }}">
                                    <input type="hidden" name="foto_jam_absen" class="image-tag">
                                    <input type="hidden" name="lat_absen" id="lat">
                                    <input type="hidden" name="long_absen" id="long">
                                    <input type="hidden" name="telat">
                                    <input type="hidden" name="jarak_masuk">
                                    <input type="hidden" name="status_absen">
                                </div>
                            </div>
                            <br>
                            <center>
                                <button type="submit" class="btn btn-primary" value="Ambil Foto" onClick="take_snapshot()">Masuk</button>
                            </center>
                            </form>
                            <br>
                    </div>
                </div>
                <br><br>

                <script type="text/javascript" src="{{ url('webcamjs/webcam.min.js') }}"></script>
                <script language="JavaScript">
                Webcam.set({
                    width: 240,
                    height: 320,
                    image_format: 'jpeg',
                    jpeg_quality: 50
                });
                Webcam.attach( '.webcam' );
                </script>
                <script language="JavaScript">
                function take_snapshot() {
                    // take snapshot and get image data
                    Webcam.snap( function(data_uri) {
                            $(".image-tag").val(data_uri);
                    // display results in page
                    document.getElementById('results').innerHTML =
                        '<img src="'+data_uri+'"/>';
                    } );
                }
                </script>

            @elseif($skjampul == null)
            <br>
            <div class="card col-lg-12">
                <div class="mt-4">
                    <form method="post" action="{{ url('/absen/pulang/'.$skid) }}">
                        @method('put')
                        @csrf
                        <div class="form-row">
                            <div class="col"></div>
                            <div class="col">
                                <center>
                                    <h2>Absen Pulang: </h2>
                                    <div class="webcam" id="results"></div>
                                </center>
                            </div>
                            <div class="col">
                                <input type="hidden" name="jam_pulang" value="{{ date('H:i') }}">
                                <input type="hidden" name="foto_jam_pulang" class="image-tag">
                                <input type="hidden" name="lat_pulang" id="lat">
                                <input type="hidden" name="long_pulang" id="long">
                                <input type="hidden" name="pulang_cepat">
                                <input type="hidden" name="jarak_pulang">
                            </div>
                        </div>
                        <br>
                        <center>
                            <button type="submit" class="btn btn-primary" value="Ambil Foto" onClick="take_snapshot()">Pulang</button>
                        </center>
                    </form>
                    <br>
                </div>
            </div>
            <br><br>

            <script type="text/javascript" src="{{ url('webcamjs/webcam.min.js') }}"></script>
            <script language="JavaScript">
            Webcam.set({
                width: 240,
                height: 320,
                image_format: 'jpeg',
                jpeg_quality: 50
            });
            Webcam.attach( '.webcam' );
            </script>
            <script language="JavaScript">
            function take_snapshot() {
                // take snapshot and get image data
                Webcam.snap( function(data_uri) {
                        $(".image-tag").val(data_uri);
                // display results in page
                document.getElementById('results').innerHTML =
                    '<img src="'+data_uri+'"/>';
                } );
            }
            </script>

            @else
            <br>
            <div class="card col-lg-12">
                <div class="mt-5">
                <div class="mb-5">
                    <center>
                        <h2>Anda Sudah Selesai Absen</h2>
                    </center>
                </div>
                </div>
            </div>

            @endif
        @endif


    </div>
    <br>
@endsection

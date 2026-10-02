<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index()
    {
        return view('lokasi.index', [
            'title' => 'Master Lokasi Kantor',
            'data_lokasi' => Lokasi::All()
        ]);
    }

    public function tambahLokasi()
    {
        return view('lokasi.tambah', [
            'title' => 'Tambah Lokasi Kantor'
        ]);
    }

    public function prosesTambahLokasi(Request $request)
    {
        $validatedData = $request->validate([
            'nama_lokasi' => 'required',
            'lat_kantor' => 'required',
            'long_kantor' => 'required',
            'radius' => 'required'
        ]);
        Lokasi::create($validatedData);
        return redirect('/lokasi-kantor')->with('success', 'Lokasi Berhasil Di Tambahkan');
    }

    public function editLokasi($id)
    {
        return view('lokasi.edit', [
            'title' => 'Edit Lokasi Kantor',
            'lokasi' => Lokasi::findOrFail($id)
        ]);
    }

    public function updateLokasi(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_lokasi' => 'required',
            'lat_kantor' => 'required',
            'long_kantor' => 'required'
        ]);

        Lokasi::where('id', $id)->update($validatedData);
        return redirect('/lokasi-kantor')->with('success', 'Lokasi Berhasil Diupdate')
;    }

    public function updateRadiusLokasi(Request $request, $id)
    {
        $validatedData = $request->validate([
            'radius' => 'required',
        ]);

        Lokasi::where('id', $id)->update($validatedData);
        return redirect('/lokasi-kantor')->with('success', 'Lokasi Berhasil Diupdate');
    }

    public function deleteLokasi($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();
        return redirect('/lokasi-kantor')->with('success', 'Lokasi Berhasil Di Delete');
    }
}

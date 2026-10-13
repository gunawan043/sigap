<?php

namespace App\Http\Controllers;

use App\Models\MasaKerja;
use Illuminate\Http\Request;

class MasaKerjaController extends Controller
{
    public function index()
    {
        $data = MasaKerja::all();
        return view('masakerja.index', compact('data'));
    }

    public function create()
    {
        return view('masakerja.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'masa_kerja' => 'required|string',
            'level' => 'required|string',
        ]);

        MasaKerja::create($request->all());

        return redirect()->route('masakerja.index')->with('success', 'Data masa kerja berhasil ditambahkan.');
    }

    public function edit(MasaKerja $masakerja)
    {
        return view('masakerja.edit', compact('masakerja'));
    }

    public function update(Request $request, MasaKerja $masakerja)
    {
        $request->validate([
            'masa_kerja' => 'required|string',
            'level' => 'required|string',
        ]);

        $masakerja->update($request->all());

        return redirect()->route('masakerja.index')->with('success', 'Data masa kerja berhasil diperbarui.');
    }

    public function destroy(MasaKerja $masakerja)
    {
        $masakerja->delete();
        return redirect()->route('masakerja.index')->with('success', 'Data masa kerja berhasil dihapus.');
    }
}

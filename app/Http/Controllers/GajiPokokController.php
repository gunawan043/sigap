<?php

namespace App\Http\Controllers;

use App\Models\GajiPokok;
use Illuminate\Http\Request;

class GajiPokokController extends Controller
{
    public function index()
    {
        $data = GajiPokok::all();
        return view('skala_gaji_guru.index', compact('data'));
    }

    public function create()
    {
        return view('skala_gaji_guru.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nominal_golongan_1' => 'nullable|string',
            'nominal_golongan_2' => 'nullable|string',
            'nominal_golongan_3' => 'nullable|string',
            'nominal_golongan_4' => 'nullable|string',
        ]);

        GajiPokok::create($request->all());

        return redirect()->route('skala-gaji-guru.index')->with('success', 'Data skala gaji guru berhasil ditambahkan.');
    }

    public function edit(GajiPokok $skala_gaji_guru)
    {
        return view('skala_gaji_guru.edit', compact('skala_gaji_guru'));
    }

    public function update(Request $request, GajiPokok $skala_gaji_guru)
    {
        $request->validate([
            'nominal_golongan_1' => 'nullable|string',
            'nominal_golongan_2' => 'nullable|string',
            'nominal_golongan_3' => 'nullable|string',
            'nominal_golongan_4' => 'nullable|string',
        ]);

        $skala_gaji_guru->update($request->all());

        return redirect()->route('skala-gaji-guru.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(GajiPokok $skala_gaji_guru)
    {
        $skala_gaji_guru->delete();
        return redirect()->route('skala-gaji-guru.index')->with('success', 'Data berhasil dihapus.');
    }
}

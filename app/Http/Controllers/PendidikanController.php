<?php

namespace App\Http\Controllers;

use App\Models\Pendidikan;
use Illuminate\Http\Request;

class PendidikanController extends Controller
{
    public function index()
    {
        $data = Pendidikan::all();
        return view('pendidikan.index', compact('data'));
    }

    public function create()
    {
        return view('pendidikan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Strata' => 'required|string',
            'Golongan' => 'required|string',
        ]);

        Pendidikan::create($request->all());

        return redirect()->route('pendidikan.index')->with('success', 'Data pendidikan berhasil ditambahkan.');
    }

    public function edit(Pendidikan $pendidikan)
    {
        return view('pendidikan.edit', compact('pendidikan'));
    }

    public function update(Request $request, Pendidikan $pendidikan)
    {
        $request->validate([
            'Strata' => 'required|string',
            'Golongan' => 'required|string',
        ]);

        $pendidikan->update($request->all());

        return redirect()->route('pendidikan.index')->with('success', 'Data pendidikan berhasil diperbarui.');
    }

    public function destroy(Pendidikan $pendidikan)
    {
        $pendidikan->delete();
        return redirect()->route('pendidikan.index')->with('success', 'Data pendidikan berhasil dihapus.');
    }
}

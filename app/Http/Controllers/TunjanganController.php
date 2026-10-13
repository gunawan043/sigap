<?php

namespace App\Http\Controllers;

use App\Models\Tunjangan;
use Illuminate\Http\Request;

class TunjanganController extends Controller
{
    public function index()
    {
        $data = Tunjangan::all();
        return view('tunjangan.index', compact('data'));
    }

    public function create()
    {
        return view('tunjangan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Kode' => 'required|string',
            'Subjek' => 'required|string',
            'Nominal' => 'required|string',
        ]);

        Tunjangan::create($request->all());

        return redirect()->route('tunjangan.index')->with('success', 'Data tunjangan berhasil ditambahkan.');
    }

    public function edit(Tunjangan $tunjangan)
    {
        return view('tunjangan.edit', compact('tunjangan'));
    }

    public function updatewoy(Request $request)
    {
        foreach ($request->tunjangan as $id => $data) {
            Tunjangan::where('id', $id)->update([
                'Subjek' => $data['Subjek'],
                'Nominal' => $data['Nominal'],
            ]);
        }

        return redirect()->back()->with('success', 'Data tunjangan berhasil diperbarui.');
    }


    public function update(Request $request, Tunjangan $tunjangan)
    {
        $request->validate([
            'Kode' => 'required|string',
            'Subjek' => 'required|string',
            'Nominal' => 'required|string',
        ]);

        $tunjangan->update($request->all());

        return redirect()->route('tunjangan.index')->with('success', 'Data tunjangan berhasil diperbarui.');
    }

    public function destroy(Tunjangan $tunjangan)
    {
        $tunjangan->delete();
        return redirect()->route('tunjangan.index')->with('success', 'Data tunjangan berhasil dihapus.');
    }
}

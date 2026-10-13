<?php

namespace App\Http\Controllers;

use App\Models\SkalaGajiAdmin;
use App\Models\GajiPokok;
use App\Models\PersentaseGaji;
use Illuminate\Http\Request;

class SkalaGajiAdminController extends Controller
{
    public function index()
    {
        $data = SkalaGajiAdmin::all();
        return view('skala_gaji_guru.index', compact('data'));
    }

    public function create()
    {
        return view('skala_gaji_guru.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'masa_kerja' => 'required|string',
            'level' => 'required|string',
            'golongan_1' => 'nullable|string',
            'golongan_2' => 'nullable|string',
            'golongan_3' => 'nullable|string',
            'golongan_4' => 'nullable|string',
        ]);

        SkalaGajiAdmin::create($request->all());

        return redirect()->route('skala-gaji-guru.index')->with('success', 'Data skala gaji guru berhasil ditambahkan.');
    }

    public function edit(SkalaGajiAdmin $skala_gaji_guru)
    {
        return view('skala_gaji_guru.edit', compact('skala_gaji_guru'));
    }

    public function update(Request $request, SkalaGajiAdmin $skala_gaji_guru)
    {
        $request->validate([
            'masa_kerja' => 'required|string',
            'level' => 'required|string',
            'golongan_1' => 'nullable|string',
            'golongan_2' => 'nullable|string',
            'golongan_3' => 'nullable|string',
            'golongan_4' => 'nullable|string',
        ]);

        $skala_gaji_guru->update($request->all());

        return redirect()->route('skala-gaji-guru.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(SkalaGajiAdmin $skala_gaji_guru)
    {
        $skala_gaji_guru->delete();
        return redirect()->route('skala-gaji-guru.index')->with('success', 'Data berhasil dihapus.');
    }

    public function updateManual(Request $request, SkalaGajiAdmin $skala_gaji_guru, $id)
    {
        $request->validate([
            'golongan_1' => 'nullable|string',
            'golongan_2' => 'nullable|string',
            'golongan_3' => 'nullable|string',
            'golongan_4' => 'nullable|string',
        ]);

        $skala_gaji_guru->update($request->all());

        $gaji = GajiPokok::where('id', 2)->first();
        $persen = PersentaseGaji::where('id', 2)->first();

        if (!$gaji || !$persen) {
            return redirect()->back()->with('error', 'Data gaji pokok atau persentase tidak ditemukan!');
        }

        $gol1 = $gaji->nominal_golongan_1;
        $gol2 = $gaji->nominal_golongan_2;
        $gol3 = $gaji->nominal_golongan_3;
        $gol4 = $gaji->nominal_golongan_4;

        $data = [];
        for ($i = 0; $i <= 15; $i++) {
            $data[] = [
                'masa_kerja' => $i,
                'level' => $this->getLevelFromMasakerja($i),
                'golongan_1' => number_format($gol1, 0, ',', '.'),
                'golongan_2' => number_format($gol2, 0, ',', '.'),
                'golongan_3' => number_format($gol3, 0, ',', '.'),
                'golongan_4' => number_format($gol4, 0, ',', '.'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Kalkulasi untuk masa kerja selanjutnya
            $gol1 *= $persen->persentase_golongan_1;
            $gol2 *= $persen->persentase_golongan_2;
            $gol3 *= $persen->persentase_golongan_3;
            $gol4 *= $persen->persentase_golongan_4;
        }

        // Bersihkan data lama dan isi ulang
        SkalaGajiAdmin::truncate();
        SkalaGajiAdmin::insert($data);

        return redirect()->back()->with('success', 'Skala Gaji Guru berhasil diperbarui!');
    }


    public function updateManualPersentase(Request $request, SkalaGajiAdmin $skala_gaji_guru, $id)
    {
        PersentaseGaji::where('id', $request->idku)->update([
            'persentase_golongan_1' => $request->persentase_golongan_1,
            'persentase_golongan_2' => $request->persentase_golongan_2,
            'persentase_golongan_3' => $request->persentase_golongan_3,
            'persentase_golongan_4' => $request->persentase_golongan_4,
        ]);

        $gaji = GajiPokok::findOrFail($id);
        $persen1 = PersentaseGaji::findOrFail(2);
        $persen3 = PersentaseGaji::findOrFail(4);
        $idnya = $request->idku;

        for ($i = 0; $i <= 15; $i++) {
            if (
                ($idnya == 2 && $i <= 5) ||
                ($idnya == 4 && $i > 5)
            ) {
                $persen = $i <= 5 ? $persen1 : $persen3;

                SkalaGajiAdmin::updateOrCreate(
                    ['masa_kerja' => $i],
                    [
                        'level' => $this->getLevelFromMasakerja($i),
                        'golongan_1' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_1, $persen->persentase_golongan_1, $i),
                            0, ',', '.'),
                        'golongan_2' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_2, $persen->persentase_golongan_2, $i),
                            0, ',', '.'),
                        'golongan_3' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_3, $persen->persentase_golongan_3, $i),
                            0, ',', '.'),
                        'golongan_4' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_4, $persen->persentase_golongan_4, $i),
                            0, ',', '.'),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Skala Gaji Guru berhasil diupdate!');
    }

    public function updateManualPokok(Request $request, SkalaGajiAdmin $skala_gaji_guru, $id)
    {
        GajiPokok::where('id', $id)->update([
            'nominal_golongan_1' => $request->nominal_golongan_1,
            'nominal_golongan_2' => $request->nominal_golongan_2,
            'nominal_golongan_3' => $request->nominal_golongan_3,
            'nominal_golongan_4' => $request->nominal_golongan_4,
        ]);

        $gaji = GajiPokok::findOrFail($id);
        $persen1 = PersentaseGaji::findOrFail(1);
        $persen3 = PersentaseGaji::findOrFail(3);
        // $idnya = $request->idku;

        for ($i = 0; $i <= 15; $i++) {
            // if (
            //     ($idnya == 1 && $i <= 5) ||
            //     ($idnya == 3 && $i > 5)
            // ) {
                $persen = $i <= 5 ? $persen1 : $persen3;

                SkalaGajiAdmin::updateOrCreate(
                    ['masa_kerja' => $i],
                    [
                        'level' => $this->getLevelFromMasakerja($i),
                        'golongan_1' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_1, $persen->persentase_golongan_1, $i),
                            0, ',', '.'),
                        'golongan_2' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_2, $persen->persentase_golongan_2, $i),
                            0, ',', '.'),
                        'golongan_3' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_3, $persen->persentase_golongan_3, $i),
                            0, ',', '.'),
                        'golongan_4' => number_format(
                            $this->hitungGaji($gaji->nominal_golongan_4, $persen->persentase_golongan_4, $i),
                            0, ',', '.'),
                        'updated_at' => now(),
                    ]
                );
            // }
        }

        return redirect()->back()->with('success', 'Skala Gaji Guru berhasil diupdate!');
    }

    private function hitungGaji($gajiPokok, $persentase, $Masakerja)
    {
        $gaji = floatval(str_replace('.', '', $gajiPokok));
        $persen = floatval(str_replace(',', '.', $persentase)) / 100;

        if ($Masakerja > 4) {
            return 2 * $gaji * pow(1 + $persen, $Masakerja - 5);
        } else {
            return $gaji * pow(1 + $persen, $Masakerja);
        }
    }

    private function getLevelFromMasakerja($i)
    {
        $levels = ['A','B','C','D','E','F'];
        return $levels[min($i, 5)];
    }
}

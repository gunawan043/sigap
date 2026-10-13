<?php

namespace App\Http\Controllers;

use App\Models\Kasbon;
use App\Models\User;
use App\Support\Angka;
use Illuminate\Http\Request;

class KasbonController extends Controller
{
    public function index()
    {
        $data = Kasbon::with('user')->latest()->get();
        return view('kasbon.index', compact('data'));
    }

    public function create()
    {
        $users = User::where('role', '!=', 'admin')->get(); // hanya non-admin
        return view('kasbon.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nominal' => 'required|string',
            'jumlah_angsuran' => 'required|string',
            'lama_angsuran' => 'nullable|string',
            'tanggal_pinjam' => 'required|string',
        ]);

        $nominal = Angka::rupiah($request->nominal);
        $angsuran = Angka::rupiah($request->jumlah_angsuran);

        if ($nominal <= 0) {
            return redirect()->back()->with('error', 'Nominal kasbon harus lebih dari 0.')->withInput();
        }

        if ($angsuran <= 0) {
            return redirect()->back()->with('error', 'Jumlah angsuran harus lebih dari 0.')->withInput();
        }

        // Satu pegawai hanya boleh punya satu kasbon berjalan.
        $kasbonBerjalan = Kasbon::where('user_id', $request->user_id)
            ->where('lunas', 0)
            ->first();

        if ($kasbonBerjalan) {
            return redirect()->back()->with('error', 'PTK masih memiliki kasbon yang belum lunas!');
        }

        // Lama angsuran dihitung otomatis bila form tidak mengisinya.
        $lamaAngsuran = (int) round(Angka::rupiah($request->lama_angsuran));
        if ($lamaAngsuran <= 0) {
            $lamaAngsuran = (int) ceil($nominal / $angsuran);
        }
        $lamaAngsuran = max(1, min(30, $lamaAngsuran));

        Kasbon::create([
            'user_id' => $request->user_id,
            'nominal' => Angka::format($nominal),
            'tanggal_pinjaman' => $request->tanggal_pinjam,
            'angsuran_perbulan' => Angka::format($angsuran),
            'terakhir_angsuran' => $request->terakhir_angsuran,
            'lama_angsuran' => $lamaAngsuran,
            'lunas' => 0,
        ]);

        return redirect()->back()->with('success', 'Data Kasbon berhasil ditambah!');
    }

    public function edit(Kasbon $kasbon)
    {
        $users = User::where('role', '!=', 'admin')->get();
        return view('kasbon.edit', compact('kasbon', 'users'));
    }

    public function update(Request $request, Kasbon $kasbon)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nominal' => 'required',
            'tanggal_pinjaman' => 'required|date',
            'angsuran_perbulan' => 'required',
        ]);

        $kasbon->update($request->all());

        return redirect()->route('kasbon.index')->with('success', 'Data kasbon berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kasbon = Kasbon::findOrFail($id);
        $kasbon->delete();
        return redirect()->back()->with('success', 'Data Kasbon berhasil dihapus!');
    }

    /**
     * Daftar kasbon milik user yang sedang login (API mobile).
     */
    public function indexMobile(Request $request)
    {
        $data = Kasbon::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get()
            ->map(function (Kasbon $kasbon) {
                $nominal = Angka::rupiah($kasbon->nominal);
                $sudahDibayar = 0;

                for ($i = 1; $i <= 30; $i++) {
                    $sudahDibayar += Angka::rupiah($kasbon->{'angsuran_ke_' . $i});
                }

                $kasbon->setAttribute('total_dibayar', Angka::format($sudahDibayar));
                $kasbon->setAttribute('sisa_pinjaman', Angka::format(max(0, $nominal - $sudahDibayar)));

                return $kasbon;
            });

        return response()->json($data);
    }

    /**
     * Pengajuan kasbon dari aplikasi mobile (user dari token).
     */
    public function storeMobile(Request $request)
    {
        $request->validate([
            'nominal' => 'required|string',
            'jumlah_angsuran' => 'required|string',
            'lama_angsuran' => 'nullable|string',
            'tanggal_pinjam' => 'nullable|string',
        ]);

        $nominal = Angka::rupiah($request->nominal);
        $angsuran = Angka::rupiah($request->jumlah_angsuran);

        if ($nominal <= 0 || $angsuran <= 0) {
            return response()->json(['message' => 'Nominal dan jumlah angsuran harus lebih dari 0.'], 422);
        }

        $userId = $request->user()->id;

        $kasbonBerjalan = Kasbon::where('user_id', $userId)->where('lunas', 0)->first();
        if ($kasbonBerjalan) {
            return response()->json(['message' => 'Masih ada kasbon yang belum lunas.'], 422);
        }

        $lamaAngsuran = (int) round(Angka::rupiah($request->lama_angsuran));
        if ($lamaAngsuran <= 0) {
            $lamaAngsuran = (int) ceil($nominal / $angsuran);
        }
        $lamaAngsuran = max(1, min(30, $lamaAngsuran));

        $kasbon = Kasbon::create([
            'user_id' => $userId,
            'nominal' => Angka::format($nominal),
            'tanggal_pinjaman' => $request->tanggal_pinjam ?: now()->format('d M, Y'),
            'angsuran_perbulan' => Angka::format($angsuran),
            'lama_angsuran' => $lamaAngsuran,
            'lunas' => 0,
        ]);

        return response()->json([
            'message' => 'Pengajuan kasbon berhasil dicatat.',
            'data' => $kasbon,
        ], 201);
    }
}

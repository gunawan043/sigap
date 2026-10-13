<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GajiPokok;
use App\Models\Pendidikan;
use App\Models\Tunjangan;
use App\Models\Masakerja;
use App\Models\SkalaGajiGuru;
use App\Models\SkalaGajiAdmin;
use App\Models\User;
use App\Models\Kasbon;
use App\Models\KitirGaji;
use App\Models\GajiPerjam;
use App\Models\KasbonInstallment;
use App\Models\Attendance;
use App\Models\Absensi;
use App\Models\ArsipKitir;
use App\Models\JamAbsensi;
use App\Support\Angka;
use Carbon\Carbon;

class KitirGajiController extends Controller
{
    /**
     * Pastikan halaman slip gaji hanya bisa dibuka oleh pemiliknya
     * (admin tetap boleh membuka semua).
     */
    private function pastikanPemilikSlip($id): void
    {
        $user = auth()->user();
        if ($user && $user->role !== 'admin' && (string) $user->id !== (string) $id) {
            abort(403, 'Anda tidak berhak melihat slip gaji pegawai lain.');
        }
    }

    public function index(Request $request, $id)
    {
        $this->pastikanPemilikSlip($id);

        // Bulan dipilih (query) atau default bulan sekarang
        $bulanAktif = $request->bulan ?? Carbon::now()->format('M, Y');

        // Ambil data gaji sesuai bulan
        $kitirbulanan = ArsipKitir::where('user_id', $id)
            ->where('priode_gaji', $bulanAktif)
            ->first();

        // Kalau tidak ada, ambil data terakhir
        if (!$kitirbulanan) {
            $kitirbulanan = ArsipKitir::where('user_id', $id)
                ->latest()
                ->first();
        }

        // List bulan yang tersedia untuk user tersebut
        $listBulan = ArsipKitir::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->pluck('priode_gaji')
            ->unique()
            ->values();

        return view('apps-invoices-user', compact(
            'kitirbulanan',
            'listBulan',
            'bulanAktif'
        ));
    }

    public function indexadmin(Request $request, $id)
    {
        // Bulan dipilih (query) atau default bulan sekarang
        $bulanAktif = $request->bulan ?? Carbon::now()->format('M, Y');

        // Ambil data gaji sesuai bulan
        $kitirbulanan = ArsipKitir::where('user_id', $id)
            ->where('priode_gaji', $bulanAktif)
            ->first();

        // Kalau tidak ada, ambil data terakhir
        if (!$kitirbulanan) {
            $kitirbulanan = ArsipKitir::where('user_id', $id)
                ->latest()
                ->first();
        }

        // List bulan yang tersedia untuk user tersebut
        $listBulan = ArsipKitir::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->pluck('priode_gaji')
            ->unique()
            ->values();

        return view('admin-invoices-user', compact(
            'kitirbulanan',
            'listBulan',
            'bulanAktif'
        ));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    // =====================================================================
    // HITUNG KITIR GAJI (STAGING)
    // =====================================================================

    public function updateKitirGaji(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) now()->month;
            $tahun = (int) now()->year;
        }

        $this->hitungDanSimpanSemuaKitir($bulan, $tahun);

        $kitirbulanan = KitirGaji::get();

        return view('admin-kitir-gaji', compact('kitirbulanan'));
    }

    /**
     * Hitung ulang kitir seluruh pegawai untuk bulan tertentu.
     */
    private function hitungDanSimpanSemuaKitir(int $bulan, int $tahun): void
    {
        $absensi = Absensi::first();
        $perjam = GajiPerjam::first();
        $hariLibur = $this->daftarHariLibur($bulan, $tahun);

        foreach (User::where('role', 'pegawai')->get() as $item) {
            $master = KitirGaji::where('user_id', $item->id)->first();
            $data = $this->hitungDataKitir($item, $master, $bulan, $tahun, $absensi, $perjam, $hariLibur);

            if ($data === null) {
                continue;
            }

            KitirGaji::updateOrCreate(['user_id' => $item->id], $data);
        }

        // Bersihkan kitir yatim (user sudah dihapus / user_id kosong).
        $idValid = User::pluck('id')->all();
        KitirGaji::where(function ($query) use ($idValid) {
            $query->whereNull('user_id');
            if (!empty($idValid)) {
                $query->orWhereNotIn('user_id', $idValid);
            }
        })->delete();
    }

    /**
     * Daftar tanggal libur (periode libur) pada bulan & tahun tertentu.
     */
    private function daftarHariLibur(int $bulan, int $tahun): array
    {
        $awal = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $akhir = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

        return \App\Support\HariLibur::daftar($awal, $akhir);
    }

    /**
     * Hitung data kitir seorang pegawai untuk periode tertentu.
     *
     * Aturan insentif (sesuai setting tabel `absensi` & data historis):
     * - Insentif Kehadiran (tunjangan_kehadiran) diberikan penuh bila tidak ada
     *   pelanggaran, lalu dikurangi:
     *     * keterlambatan  : akumulasi menit telat / (toleransi/4) x pot_terlambat
     *     * lupa absen pulang: tiap kejadian menambah `pot_absen` menit ke akumulasi
     *     * tidak absen (alfa) : tiap hari menambah `pot_absen` menit ke akumulasi
     *   Nilai akhir tidak pernah negatif.
     * - Insentif Kedisiplinan (tunjangan_harian) diberikan penuh untuk pegawai
     *   induk Sekolah/Admin (bukan Yayasan/TPQ), sesuai data historis aplikasi.
     */
    private function hitungDataKitir(
        User $item,
        ?KitirGaji $master,
        int $bulan,
        int $tahun,
        ?Absensi $absensi,
        ?GajiPerjam $perjam,
        array $hariLibur
    ): ?array {
        if (!$item->awal_masuk || !$item->pendidikan) {
            return null;
        }

        try {
            $awalMasuk = Carbon::createFromFormat('d M, Y', $item->awal_masuk);
        } catch (\Throwable $e) {
            return null;
        }

        $now = Carbon::now();
        $masaKerjaTahun = (int) floor($awalMasuk->floatDiffInYears($now));

        $masaKerjaLevel = $masaKerjaTahun > 5
            ? (object) ['level' => 'F']
            : Masakerja::where('masa_kerja', $masaKerjaTahun)->first();

        $golpt = Pendidikan::where('Strata', $item->pendidikan)->first();
        $golonganAngka = $golpt ? filter_var($golpt->Golongan, FILTER_SANITIZE_NUMBER_INT) : null;
        $txtgolongan = 'golongan_' . $golonganAngka;

        if ($item->induk == 'Admin') {
            $skala = SkalaGajiAdmin::where('masa_kerja', $masaKerjaTahun)->first();
        } else {
            $skala = SkalaGajiGuru::where('masa_kerja', $masaKerjaTahun)->first();
        }

        $nilaiGaji = $skala ? ($skala->{$txtgolongan} ?? null) : null;

        $bukanYayasan = !in_array($item->induk, ['Yayasan', 'TPQ'], true);

        if ($bukanYayasan) {
            $hasilKehadiran = $this->hitungInsentifKehadiran(
                $item->id,
                $awalMasuk,
                $bulan,
                $tahun,
                $absensi,
                $hariLibur
            );

            $insentifKehadiran = $hasilKehadiran['insentif'];
            $insentifKedisiplinan = Angka::rupiah($absensi->tunjangan_harian ?? 0);
        } else {
            // Yayasan & TPQ: tunjangan jabatan saja (tidak ikut absensi).
            $nilaiGaji = 0;
            $insentifKehadiran = 0;
            $insentifKedisiplinan = 0;
        }

        if ($nilaiGaji === null) {
            $nilaiGaji = 0;
        }

        // Kode tunjangan jabatan: pakai pilihan admin kalau sudah ada,
        // kalau belum fallback ke subjek pegawai (perilaku lama).
        $kodeKj = [];
        $kodeKj[1] = ($master && ($master->kj1 ?? '') !== '') ? $master->kj1 : $item->subjek;
        for ($i = 2; $i <= 5; $i++) {
            $kodeKj[$i] = $master->{'kj' . $i} ?? '';
        }

        $totalTjab = 0;
        for ($i = 1; $i <= 5; $i++) {
            if (empty($kodeKj[$i])) {
                continue;
            }
            $tunjangan = Tunjangan::where('Kode', $kodeKj[$i])->first();
            $totalTjab += Angka::rupiah($tunjangan->Nominal ?? 0);
        }

        $gajiPokok = Angka::rupiah($nilaiGaji);
        $totalGaji = $gajiPokok + $totalTjab;

        // Jam mengajar & potongan insidentil adalah input manual admin,
        // jadi dibaca dari data master yang sudah ada.
        $jamMengajar = Angka::rupiah($master->jam_mengajar ?? 0);
        $rpPerJam = Angka::rupiah($perjam->rp_gaji ?? 0);
        $totalRpJam = $rpPerJam * $jamMengajar;
        $uangLaukPauk = $perjam->lauk_pauk ?? 0;
        $potInsidentil = Angka::rupiah($master->pot_insidentil ?? 0);
        $potBos = Angka::rupiah($item->dana_bos ?? 0);

        // Kasbon: hitung angsuran bulan ini (belum dipotong dari ledger).
        $kasbonAktif = $this->hitungKasbonAktif($item->id);
        $potKasbon = $kasbonAktif['potongan'];
        $totalPotongan = $potKasbon + $potBos + $potInsidentil;

        return [
            'nama_ptk' => $item->name,
            'jk' => $item->jk,
            'induk' => $item->induk,
            'pt' => $item->pendidikan,
            'gol_pt' => ($golpt->Golongan ?? '') . ($masaKerjaLevel->level ?? ''),
            'gaji_pokok' => $nilaiGaji,
            'total_gaji' => Angka::format($totalGaji),
            'mk' => $masaKerjaTahun,
            'tmt' => $item->awal_masuk,
            't_jab' => Angka::format($totalTjab),
            'total_rp_jam' => Angka::format($totalRpJam),
            'pot_bos' => $item->dana_bos,
            'pot_kasbon' => $potKasbon > 0 ? Angka::format($potKasbon) : null,
            'total_potongan' => Angka::format($totalPotongan),
            'insentif_kehadiran' => $insentifKehadiran,
            'tunjangan_kinerja' => $insentifKedisiplinan > 0 ? Angka::format($insentifKedisiplinan) : 0,
            'uang_lauk_pauk' => $uangLaukPauk,
            'kj1' => $kodeKj[1],
            'kj2' => $kodeKj[2],
            'kj3' => $kodeKj[3],
            'kj4' => $kodeKj[4],
            'kj5' => $kodeKj[5],
            'jam_mengajar' => $master->jam_mengajar ?? null,
            'pot_insidentil' => $master->pot_insidentil ?? null,
        ];
    }

    /**
     * Hitung insentif kehadiran dari absensi pegawai pada bulan tertentu.
     * Logikanya ada di App\Support\InsentifKehadiran supaya dipakai bersama
     * oleh penggajian, halaman rekap, dan command pemeriksa.
     */
    private function hitungInsentifKehadiran(
        $userId,
        Carbon $awalMasuk,
        int $bulan,
        int $tahun,
        ?Absensi $absensi,
        array $hariLibur
    ): array {
        return \App\Support\InsentifKehadiran::hitung(
            $userId,
            $awalMasuk,
            $bulan,
            $tahun,
            $absensi,
            $hariLibur
        );
    }

    /**
     * Hitung angsuran kasbon yang seharusnya dipotong bulan ini.
     */
    private function hitungKasbonAktif($userId): array
    {
        $kasbon = Kasbon::where('user_id', $userId)
            ->where('lunas', 0)
            ->orderBy('id')
            ->first();

        if (!$kasbon) {
            return ['kasbon' => null, 'potongan' => 0.0, 'sisa' => 0.0];
        }

        $nominal = Angka::rupiah($kasbon->nominal);
        $sudahDibayar = 0;

        for ($i = 1; $i <= 30; $i++) {
            $sudahDibayar += Angka::rupiah($kasbon->{'angsuran_ke_' . $i});
        }

        $sisa = max(0, $nominal - $sudahDibayar);
        $angsuran = Angka::rupiah($kasbon->angsuran_perbulan);
        $potongan = min($angsuran, $sisa);

        return ['kasbon' => $kasbon, 'potongan' => $potongan, 'sisa' => $sisa];
    }

    // =====================================================================
    // PUBLISH GAJI
    // =====================================================================

    public function publishGaji(Request $request)
    {
        $request->validate([
            'priode' => 'required|string',
        ]);

        $periodeFormatted = $request->priode;

        try {
            $periode = Carbon::createFromFormat('M, Y', $periodeFormatted)->startOfMonth();
        } catch (\Throwable $e) {
            return back()->with('error', 'Format periode tidak valid. Contoh yang benar: Sep, 2026');
        }

        $batasTanggal = $periode->copy()->addMonth()->day(15)->endOfDay();
        if (now()->greaterThan($batasTanggal)) {
            return back()->with(
                'error',
                'Gagal publish/update: periode sudah melewati batas tanggal 15 bulan berikutnya!'
            );
        }

        $bulan = (int) $periode->month;
        $tahun = (int) $periode->year;

        $absensi = Absensi::first();
        $perjam = GajiPerjam::first();
        $hariLibur = $this->daftarHariLibur($bulan, $tahun);

        // Cek SEBELUM proses: apakah periode ini sudah pernah dipublish?
        $existing = ArsipKitir::where('priode_gaji', $periodeFormatted)->exists();

        foreach (KitirGaji::whereNotNull('user_id')->get() as $item) {
            $user = User::find($item->user_id);
            if (!$user || $user->role !== 'pegawai') {
                continue;
            }

            $data = $this->hitungDataKitir($user, $item, $bulan, $tahun, $absensi, $perjam, $hariLibur);
            if ($data === null) {
                continue;
            }

            $arsipLama = ArsipKitir::where('user_id', $user->id)
                ->where('priode_gaji', $periodeFormatted)
                ->first();

            $potKasbon = Angka::rupiah($data['pot_kasbon'] ?? 0);

            // Ledger (kasbon_installments) adalah acuan utama:
            // kalau periode ini sudah pernah dipotong, pakai nilai itu dan jangan potong lagi.
            $ledger = KasbonInstallment::where('user_id', $user->id)
                ->where('bulan', $periodeFormatted)
                ->first();

            if ($ledger) {
                $potKasbon = Angka::rupiah($ledger->jumlah);
            } elseif ($arsipLama) {
                // Periode sudah pernah dipublish tetapi belum tercatat di ledger
                // (data lama): jangan potong ulang, pakai nilai yang dihitung.
            } else {
                // Periode baru: catat & potong angsuran kasbon (sekali saja).
                $potKasbon = $this->catatPotonganKasbon(
                    $user->id,
                    $periodeFormatted,
                    $potKasbon,
                    $item->id
                );
            }

            $potBos = Angka::rupiah($data['pot_bos'] ?? 0);
            $potInsidentil = Angka::rupiah($data['pot_insidentil'] ?? 0);
            $potKasbon = Angka::rupiah($potKasbon);

            $data['pot_kasbon'] = $potKasbon > 0 ? Angka::format($potKasbon) : null;
            $data['total_potongan'] = Angka::format($potKasbon + $potBos + $potInsidentil);

            // Sinkronkan data master (halaman Kitir Gaji Bulanan).
            $item->update($data);
            $kitir = $item->fresh();

            $totalTanpaPotongan =
                Angka::rupiah($kitir->total_gaji) +
                Angka::rupiah($kitir->total_rp_jam) +
                Angka::rupiah($kitir->insentif_kehadiran) +
                Angka::rupiah($kitir->tunjangan_kinerja) +
                Angka::rupiah($kitir->uang_lauk_pauk);

            $totalDiterima = $totalTanpaPotongan -
                (Angka::rupiah($kitir->pot_kasbon) +
                 Angka::rupiah($kitir->pot_bos) +
                 Angka::rupiah($kitir->pot_insidentil));

            ArsipKitir::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'priode_gaji' => $periodeFormatted,
                ],
                [
                    'nama_ptk' => $kitir->nama_ptk,
                    'induk' => $kitir->induk,
                    'tmt' => $kitir->tmt,
                    'jk' => $kitir->jk,
                    'gol_pt' => $kitir->gol_pt,
                    'gaji_pokok' => $kitir->gaji_pokok,
                    'kj1' => $kitir->kj1,
                    'kj2' => $kitir->kj2,
                    'kj3' => $kitir->kj3,
                    'kj4' => $kitir->kj4,
                    'kj5' => $kitir->kj5,
                    't_jab' => $kitir->t_jab,
                    'total_gaji' => $kitir->total_gaji,
                    'jam_mengajar' => $kitir->jam_mengajar,
                    'tunjangan_kinerja' => $kitir->tunjangan_kinerja,
                    'total_rp_jam' => $kitir->total_rp_jam,
                    'insentif_kehadiran' => $kitir->insentif_kehadiran,
                    'uang_lauk_pauk' => $kitir->uang_lauk_pauk,
                    'pot_kasbon' => $kitir->pot_kasbon,
                    'pot_bos' => $kitir->pot_bos,
                    'pot_insidentil' => $kitir->pot_insidentil,
                    'total_potongan' => $kitir->total_potongan,
                    'total_tanpa_potongan' => Angka::format($totalTanpaPotongan),
                    'total_diterima' => Angka::format($totalDiterima),
                    'updated_at' => now(),
                ]
            );
        }

        return back()->with(
            'success',
            $existing
                ? 'Data berhasil diupdate untuk periode ini!'
                : 'Data berhasil dipublish!'
        );
    }

    /**
     * Catat satu angsuran kasbon pada satu periode gaji.
     * Idempoten: satu periode hanya dipotong satu kali.
     *
     * @return float nominal yang benar-benar dicatat
     */
    private function catatPotonganKasbon($userId, string $periodeLabel, float $potonganDiajukan, $kitirId): float
    {
        return DB::transaction(function () use ($userId, $periodeLabel, $potonganDiajukan, $kitirId) {
            $kasbon = Kasbon::where('user_id', $userId)
                ->where('lunas', 0)
                ->orderBy('id')
                ->first();

            if (!$kasbon) {
                return 0.0;
            }

            $sudahTercatat = KasbonInstallment::where('kasbon_id', $kasbon->id)
                ->where('bulan', $periodeLabel)
                ->exists();

            if ($sudahTercatat) {
                return 0.0;
            }

            $nominal = Angka::rupiah($kasbon->nominal);
            $sudahDibayar = 0;

            for ($i = 1; $i <= 30; $i++) {
                $sudahDibayar += Angka::rupiah($kasbon->{'angsuran_ke_' . $i});
            }

            $sisa = $nominal - $sudahDibayar;

            if ($sisa <= 0) {
                $kasbon->lunas = 1;
                $kasbon->save();

                return 0.0;
            }

            $angsuran = Angka::rupiah($kasbon->angsuran_perbulan);
            $potongan = min($potonganDiajukan > 0 ? $potonganDiajukan : $angsuran, $sisa);

            if ($potongan <= 0) {
                return 0.0;
            }

            // Isi kolom angsuran pertama yang masih kosong (kompatibilitas tampilan lama).
            for ($i = 1; $i <= 30; $i++) {
                $kolom = 'angsuran_ke_' . $i;
                $nilai = $kasbon->{$kolom};

                if ($nilai === null || $nilai === '') {
                    $kasbon->{$kolom} = Angka::format($potongan);
                    break;
                }
            }

            $kasbon->terakhir_angsuran = now();

            if (($sudahDibayar + $potongan) >= $nominal) {
                $kasbon->lunas = 1;
            }

            $kasbon->save();

            KasbonInstallment::create([
                'kasbon_id' => $kasbon->id,
                'user_id' => $userId,
                'kitir_id' => $kitirId,
                'bulan' => $periodeLabel,
                'jumlah' => Angka::format($potongan),
                'terpotong' => true,
            ]);

            return $potongan;
        });
    }

    // =====================================================================
    // EDIT MANUAL KITIR
    // =====================================================================

    public function updatekitir(Request $request, $id)
    {
        $request->validate([
            'kj1' => 'nullable|string',
            'kj2' => 'nullable|string',
            'kj3' => 'nullable|string',
            'kj4' => 'nullable|string',
            'kj5' => 'nullable|string',
            'jam_mengajar' => 'nullable|string',
            'pot_insidentil' => 'nullable|string',
        ]);

        $kitir = KitirGaji::findOrFail($id);

        $kode = [
            1 => $request->kj1,
            2 => $request->kj2,
            3 => $request->kj3,
            4 => $request->kj4,
            5 => $request->kj5,
        ];

        $totalTjab = 0;
        foreach ($kode as $kodeTunjangan) {
            if (empty($kodeTunjangan)) {
                continue;
            }
            $tunjangan = Tunjangan::where('Kode', $kodeTunjangan)->first();
            $totalTjab += Angka::rupiah($tunjangan->Nominal ?? 0);
        }

        $gajiPokok = Angka::rupiah($kitir->gaji_pokok);
        $rpPerJam = Angka::rupiah(optional(GajiPerjam::first())->rp_gaji ?? 0);
        $jamMengajar = Angka::rupiah($request->jam_mengajar);
        $potInsidentil = Angka::rupiah($request->pot_insidentil);
        $potKasbon = Angka::rupiah($kitir->pot_kasbon);
        $potBos = Angka::rupiah($kitir->pot_bos);

        $kitir->update([
            'kj1' => $request->kj1,
            'kj2' => $request->kj2,
            'kj3' => $request->kj3,
            'kj4' => $request->kj4,
            'kj5' => $request->kj5,
            'pot_insidentil' => $request->pot_insidentil,
            'jam_mengajar' => $request->jam_mengajar,
            'total_rp_jam' => Angka::format($rpPerJam * $jamMengajar),
            't_jab' => Angka::format($totalTjab),
            'total_gaji' => Angka::format($gajiPokok + $totalTjab),
            'total_potongan' => Angka::format($potKasbon + $potBos + $potInsidentil),
        ]);

        return redirect()->back()->with('success', 'Data kitir gaji berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        //
    }
}

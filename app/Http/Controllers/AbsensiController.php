<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\JamAbsensi;
use App\Models\Absensi;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->latest()->get();
        $JadwalAbsensi = JamAbsensi::jadwalHarian()->get();
        $periodeLibur = JamAbsensi::periodeLibur()->get();
        $lokasi = Location::first();

        return view('admin-setting-absensi', compact('attendances', 'JadwalAbsensi', 'periodeLibur', 'lokasi'));
    }

    public function storePeriodeLibur(Request $request)
    {
        $request->validate([
            'jenis' => 'nullable|in:nasional,sekolah,lainnya',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan_libur' => 'required|string|max:255'
        ]);

        // Cek apakah periode libur bertabrakan dengan yang sudah ada
        $existingLibur = JamAbsensi::where('is_periode_libur', true)
            ->where(function ($query) use ($request) {
                $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai])
                    ->orWhereBetween('tanggal_selesai', [$request->tanggal_mulai, $request->tanggal_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('tanggal_mulai', '<=', $request->tanggal_mulai)
                            ->where('tanggal_selesai', '>=', $request->tanggal_selesai);
                    });
            })
            ->first();

        if ($existingLibur) {
            return redirect()->back()->with('error', 'Periode libur ini bertabrakan dengan periode libur yang sudah ada.');
        }

        // Susun label sesuai jenis libur
        $label = $request->keterangan_libur;
        if ($request->jenis === 'nasional') {
            $label = 'Libur Nasional: ' . $request->keterangan_libur;
        } elseif ($request->jenis === 'sekolah') {
            $label = 'Libur Sekolah: ' . $request->keterangan_libur;
        }

        // Simpan periode libur
        JamAbsensi::create([
            'hari' => $request->tanggal_mulai === $request->tanggal_selesai ? $request->tanggal_mulai : 'Libur Periode',
            'check_in' => '00:00',
            'check_out' => '00:00',
            'status' => 'Nonactive',
            'is_periode_libur' => true,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan_libur' => $label
        ]);

        // Generate attendance otomatis untuk semua user selama periode libur
        $this->generateAttendanceForTanggal(
            $request->tanggal_mulai,
            $request->tanggal_selesai
        );

        return $this->kembaliDenganPeringatanPublish(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
            'Periode libur berhasil disimpan dan data kehadiran otomatis diisi. Pegawai yang tetap masuk pada tanggal tersebut tidak terpengaruh.'
        );
    }

    /**
     * Generate hari libur nasional dari config/hari_libur.php.
     */
    public function generateLiburNasional(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2020|max:2035',
        ]);

        $daftar = config('hari_libur.' . (int) $request->tahun, []);

        if (empty($daftar)) {
            return redirect()->back()->with(
                'error',
                'Daftar libur nasional tahun ' . $request->tahun . ' belum tersedia. Tambahkan dulu di config/hari_libur.php.'
            );
        }

        $dibuat = 0;

        foreach ($daftar as $tanggal => $nama) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $tanggal)) {
                continue;
            }

            // Lewati kalau sudah tercakup periode libur lain
            $sudahAda = JamAbsensi::where('is_periode_libur', true)
                ->where('tanggal_mulai', '<=', $tanggal)
                ->where('tanggal_selesai', '>=', $tanggal)
                ->exists();

            if ($sudahAda) {
                continue;
            }

            JamAbsensi::create([
                'hari' => 'Libur Nasional',
                'check_in' => '00:00',
                'check_out' => '00:00',
                'status' => 'Nonactive',
                'is_periode_libur' => true,
                'tanggal_mulai' => $tanggal,
                'tanggal_selesai' => $tanggal,
                'keterangan_libur' => 'Libur Nasional: ' . $nama,
            ]);

            $this->generateAttendanceForTanggal($tanggal, $tanggal);
            $dibuat++;
        }

        $pesan = $dibuat > 0
            ? $dibuat . ' hari libur nasional tahun ' . $request->tahun . ' berhasil ditambahkan. Mohon verifikasi dengan SKB 3 Menteri terbaru dan hapus bila ada tanggal yang tidak sesuai.'
            : 'Semua libur nasional tahun ' . $request->tahun . ' sudah ada di sistem.';

        return $this->kembaliDenganPeringatanPublish(
            $request->tahun . '-01-01',
            $request->tahun . '-12-31',
            $pesan
        );
    }

    /**
     * Cek periode gaji yang sudah dipublish pada rentang tanggal.
     *
     * @return string[] label periode, mis. ["Sep, 2026"]
     */
    private function periodeSudahPublish($tanggalMulai, $tanggalSelesai): array
    {
        $hasil = [];
        $kursor = Carbon::parse($tanggalMulai)->startOfMonth();
        $akhir = Carbon::parse($tanggalSelesai)->startOfMonth();

        while ($kursor->lte($akhir)) {
            $label = $kursor->format('M, Y');

            if (\App\Models\ArsipKitir::where('priode_gaji', $label)->exists()) {
                $hasil[] = $label;
            }

            $kursor->addMonth();
        }

        return array_values(array_unique($hasil));
    }

    /**
     * Redirect dengan pesan sukses + peringatan publish ulang bila perlu.
     */
    private function kembaliDenganPeringatanPublish($tanggalMulai, $tanggalSelesai, string $pesanSukses)
    {
        $redirect = redirect()->back()->with('success', $pesanSukses);
        $periode = $this->periodeSudahPublish($tanggalMulai, $tanggalSelesai);

        if (!empty($periode)) {
            $redirect->with(
                'warning',
                'Periode gaji berikut sudah pernah dipublish: ' . implode(', ', $periode) .
                '. Silakan buka Kitir Gaji Bulanan lalu Publish Gaji ulang untuk periode tersebut supaya perhitungan insentif kehadiran ikut diperbaiki.'
            );
        }

        return $redirect;
    }

    /**
     * Buat penanda kehadiran untuk rentang tanggal libur/tidak aktif.
     * Status "-" dipakai sebagai penanda libur (bukan alfa).
     */
    private function generateAttendanceForTanggal($tanggalMulai, $tanggalSelesai): void
    {
        $start = Carbon::parse($tanggalMulai);
        $end = Carbon::parse($tanggalSelesai);
        $users = User::where('role', 'pegawai')->get();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $tanggal = $date->format('Y-m-d');

            $sudahAda = Attendance::whereDate('date', $tanggal)->pluck('user_id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $data = [];
            foreach ($users as $user) {
                if (in_array((string) $user->id, $sudahAda, true)) {
                    continue;
                }

                $data[] = [
                    'user_id' => $user->id,
                    'date' => $tanggal,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => '-',
                    'is_late' => 0,
                    'selisih_menit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($data)) {
                DB::table('attendances')->insert($data);
            }
        }
    }

    public function deletePeriodeLibur($id)
    {
        $periode = JamAbsensi::findOrFail($id);

        $mulai = $periode->tanggal_mulai;
        $selesai = $periode->tanggal_selesai;

        // Hapus data attendance penanda libur pada rentang ini
        $this->deleteAttendancePenanda($mulai, $selesai);

        $periode->delete();

        return $this->kembaliDenganPeringatanPublish(
            $mulai ?? now()->toDateString(),
            $selesai ?? now()->toDateString(),
            'Periode libur berhasil dihapus dan data kehadiran dibersihkan.'
        );
    }

    /**
     * Hapus baris attendance penanda libur (status "-") pada rentang tanggal.
     */
    private function deleteAttendancePenanda($tanggalMulai, $tanggalSelesai): void
    {
        Attendance::whereBetween('date', [$tanggalMulai, $tanggalSelesai])
            ->where('status', '-')
            ->delete();
    }

    // Fungsi untuk mengecek apakah tanggal termasuk libur
    public function isHoliday($tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        return JamAbsensi::cekTanggalLibur($tanggal);
    }

    // Fungsi untuk mendapatkan jadwal yang aktif berdasarkan tanggal
    public function getActiveSchedule($tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        $status = \App\Support\HariLibur::status($tanggal);

        return (object) [
            'check_in' => $status['check_in'],
            'check_out' => $status['check_out'],
            'status' => $status['libur'] ? 'Nonactive' : 'Active',
            'is_libur' => $status['libur'],
            'keterangan' => $status['keterangan'],
        ];
    }

    // Cek ketersediaan absensi pada tanggal tertentu
    public function checkAbsensiAvailability($tanggal)
    {
        $status = \App\Support\HariLibur::status($tanggal);

        if ($status['libur']) {
            return [
                'allowed' => false,
                'message' => 'Absensi tidak aktif pada tanggal ini. ' . (!empty($status['keterangan']) ? 'Keterangan: ' . $status['keterangan'] : '')
            ];
        }

        return [
            'allowed' => true,
            'check_in' => $status['check_in'],
            'check_out' => $status['check_out']
        ];
    }

    public function storePengaturanTanggal(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|in:aktif,non_aktif',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string'
        ]);

        $tanggal = $request->tanggal;

        // Cek apakah tanggal sudah punya pengaturan
        $existing = JamAbsensi::where('hari', $tanggal)->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Tanggal ini sudah ada pengaturan.');
        }

        // Format jam ke HH:i:s agar konsisten dengan data jadwal
        $checkIn = $request->check_in ? (strlen($request->check_in) === 5 ? $request->check_in . ':00' : $request->check_in) : null;
        $checkOut = $request->check_out ? (strlen($request->check_out) === 5 ? $request->check_out . ':00' : $request->check_out) : null;

        $nonAktif = $request->status === 'non_aktif';

        // Simpan sebagai pengaturan khusus tanggal (kolom `hari` berisi tanggal).
        JamAbsensi::create([
            'hari' => $tanggal,
            'check_in' => $checkIn ?? '07:00:00',
            'check_out' => $checkOut ?? '14:00:00',
            'status' => $nonAktif ? 'Nonactive' : 'Active',
            'is_periode_libur' => $nonAktif,
            'tanggal_mulai' => $nonAktif ? $tanggal : null,
            'tanggal_selesai' => $nonAktif ? $tanggal : null,
            'keterangan_libur' => $request->keterangan,
        ]);

        // Jika non-aktif, tandai kehadiran semua user dengan "-" (libur, bukan alfa)
        if ($nonAktif) {
            $this->generateAttendanceForTanggal($tanggal, $tanggal);
        }

        return $this->kembaliDenganPeringatanPublish(
            $tanggal,
            $tanggal,
            'Pengaturan tanggal berhasil disimpan.'
        );
    }

    public function deletePengaturanTanggal($id)
    {
        $jadwal = JamAbsensi::findOrFail($id);

        // Hapus baris penanda libur pada tanggal tersebut
        if ($jadwal->hari && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $jadwal->hari)) {
            Attendance::whereDate('date', $jadwal->hari)->where('status', '-')->delete();
        }

        $jadwal->delete();

        return redirect()->back()->with('success', 'Pengaturan tanggal berhasil dihapus.');
    }

    // Handle hari Minggu secara otomatis (non-aktif)
    public function handleSundayAutomatically()
    {
        if (date('w') != 0) {
            return redirect()->back()->with('success', 'Hari ini bukan hari Minggu.');
        }

        $existing = JamAbsensi::where('hari', 'Minggu')->first();

        if (!$existing) {
            JamAbsensi::create([
                'hari' => 'Minggu',
                'check_in' => '07:00:00',
                'check_out' => '14:00:00',
                'status' => 'Nonactive',
                'is_periode_libur' => false,
                'keterangan_libur' => 'Hari Minggu - Libur'
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan hari Minggu berhasil disimpan.');
    }

    public function create()
    {
        $users = User::where('role', '!=', 'admin')->get();
        return view('attendance.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'status' => 'required|in:hadir,telat,izin,alpha',
            'is_late' => 'boolean',
            'selisih_menit' => 'nullable|integer',
        ]);

        Attendance::create($request->all());

        return redirect()->route('attendance.index')->with('success', 'Data kehadiran berhasil ditambahkan.');
    }

    public function edit(Attendance $attendance)
    {
        $users = User::where('role', '!=', 'admin')->get();
        return view('attendance.edit', compact('attendance', 'users'));
    }

    public function updatejadwal(Request $request)
    {
        $checkIns = $request->input('check_in');
        $checkOuts = $request->input('check_out');
        $statuses = $request->input('status');

        // Ambil hanya jadwal harian (bukan pengaturan khusus tanggal/periode libur)
        $jadwals = JamAbsensi::where('is_periode_libur', false)
            ->whereIn('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])
            ->orderBy('id')
            ->get();

        foreach ($jadwals as $index => $jadwal) {
            $jadwal->check_in = $checkIns[$index] ?? $jadwal->check_in;
            $jadwal->check_out = $checkOuts[$index] ?? $jadwal->check_out;
            $jadwal->status = $statuses[$index] ?? $jadwal->status;
            $jadwal->save();
        }

        return redirect()->back()->with('success', 'Jadwal absensi berhasil diperbarui.');
    }

    public function updatelokasi(Request $request)
    {
        $request->validate([
            'radius_meter' => 'required|numeric',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'tunjangan_kehadiran' => 'required|numeric',
            'toleransi_keterlambatan' => 'required|numeric',
            'pot_terlambat' => 'required|numeric',
            'pot_absen' => 'required|numeric',
            'tunjangan_harian' => 'required|numeric',
        ]);

        $lokasi = Location::first();
        $absensi = Absensi::first();

        if ($lokasi) {
            $lokasi->radius_meter = $request->radius_meter;
            $lokasi->latitude = $request->latitude;
            $lokasi->longitude = $request->longitude;
            $lokasi->save();
        }

        if ($absensi) {
            $absensi->tunjangan_kehadiran = $request->tunjangan_kehadiran;
            $absensi->toleransi_keterlambatan = $request->toleransi_keterlambatan;
            $absensi->pot_terlambat = $request->pot_terlambat;
            $absensi->tunjangan_harian = $request->tunjangan_harian;
            $absensi->pot_absen = $request->pot_absen;
            $absensi->save();
        }

        return redirect()->back()->with('success', 'Data lokasi dan absensi berhasil diupdate.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index')->with('success', 'Data kehadiran berhasil dihapus.');
    }
}

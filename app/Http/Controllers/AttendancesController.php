<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\JamAbsensi;
use App\Models\ArsipKitir;
use App\Models\KitirGaji;
use App\Models\Location;
use App\Support\Lokasi;
use App\Support\HariLibur;

class AttendancesController extends Controller
{
    /**
     * Validasi radius absensi di server (bukan hanya di browser).
     * Mengembalikan pesan error, atau null kalau valid.
     */
    private function periksaRadius($latitude, $longitude): ?string
    {
        $lokasi = Location::first();

        if (!$lokasi || $lokasi->latitude === null || $lokasi->longitude === null) {
            return null;
        }

        $radius = (float) ($lokasi->radius_meter ?? 0);

        if ($radius <= 0) {
            return null;
        }

        if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
            return 'Lokasi Anda belum terdeteksi. Aktifkan GPS dan coba lagi.';
        }

        $jarak = Lokasi::jarakMeter($latitude, $longitude, $lokasi->latitude, $lokasi->longitude);

        if ($jarak > $radius) {
            return 'Anda berada di luar radius absensi (' . round($jarak) . ' meter dari lokasi).';
        }

        return null;
    }

    public function submitAbsenyadong(Request $request)
    {
        $request->validate([
            'type' => 'required|in:masuk,pulang',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $errorRadius = $this->periksaRadius($request->latitude, $request->longitude);
        if ($errorRadius) {
            return response()->json(['message' => $errorRadius], 422);
        }

        $user = Auth::user();
        $tipe = $request->input('type');
        $jamSekarang = Carbon::now('Asia/Makassar');
        $tanggal = $jamSekarang->toDateString();
        $jam = $jamSekarang->format('H:i:s');

        if (!in_array($tipe, ['masuk', 'pulang'], true)) {
            return response()->json(['message' => 'Tipe absensi tidak valid.'], 400);
        }

        // Cek status hari (libur nasional / libur sekolah / pengaturan tanggal / jadwal harian)
        $statusHari = HariLibur::status($tanggal);

        if ($statusHari['libur']) {
            $sudahMasuk = Attendance::where('user_id', $user->id)->where('date', $tanggal)->first();

            // Absen pulang tetap diizinkan bila pegawai sudah absen masuk sebelum
            // tanggal tersebut ditandai libur (agar catatannya bisa diselesaikan).
            $bolehPulang = $tipe === 'pulang' && $sudahMasuk && !empty($sudahMasuk->check_in_time);

            if (!$bolehPulang) {
                return response()->json([
                    'message' => 'Absensi tidak aktif pada tanggal ini.' . (!empty($statusHari['keterangan']) ? ' Keterangan: ' . $statusHari['keterangan'] : '')
                ], 422);
            }
        }

        // Tentukan jam check-in atau check-out dengan toleransi
        if ($tipe === 'masuk') {
            if (empty($statusHari['check_in'])) {
                return response()->json(['message' => 'Jadwal absensi belum diatur untuk tanggal ini.'], 404);
            }

            $jamCheck = Carbon::createFromFormat('H:i:s', $statusHari['check_in'], 'Asia/Makassar');
            $batasToleransi = $jamCheck->copy()->addMinute(); // 07:15:00 - 07:15:59
        } else {
            $jamCheck = !empty($statusHari['check_out'])
                ? Carbon::createFromFormat('H:i:s', $statusHari['check_out'], 'Asia/Makassar')
                : $jamSekarang->copy();
            $batasToleransi = $jamCheck;
        }
    
        // Cek IP sudah dipakai user lain (opsional)
        // $cekIP = Attendance::where('status', $ipAddress)
        //     ->where('date', $tanggal)
        //     ->where('user_id', '!=', $user->id)
        //     ->exists();
    
        // Hitung keterlambatan hanya jika tipe masuk dengan mempertimbangkan toleransi
        $isLate = 0;
        $selisihMenit = 0;
        if ($tipe === 'masuk') {
            // Cek apakah jam sekarang >= batas toleransi (07:16:00 ke atas)
            if ($jamSekarang->greaterThanOrEqualTo($batasToleransi)) {
                $isLate = 1;
                // Hitung selisih dari jam check_in yang sebenarnya, bukan dari batas toleransi
                $selisihMenit = $jamCheck->diffInMinutes($jamSekarang);
            }
        }
    
        // Ambil absensi hari ini untuk user saat ini
        $existingAttendance = Attendance::where('user_id', $user->id)->where('date', $tanggal)->first();

        // Cegah absen ganda & absen pulang tanpa absen masuk
        if ($tipe === 'masuk' && $existingAttendance && !empty($existingAttendance->check_in_time)) {
            return response()->json(['message' => 'Anda sudah absen masuk hari ini.'], 400);
        }

        if ($tipe === 'pulang') {
            if (!$existingAttendance || empty($existingAttendance->check_in_time)) {
                return response()->json(['message' => 'Silakan absen masuk terlebih dahulu sebelum absen pulang.'], 400);
            }

            if (!empty($existingAttendance->check_out_time)) {
                return response()->json(['message' => 'Anda sudah absen pulang hari ini.'], 400);
            }
        }

        // Kolom status harus berisi status kehadiran (hadir/telat/izin/alpha),
        // BUKAN alamat IP. Data lama yang berisi IP akan dinormalkan.
        $statusLama = $existingAttendance->status ?? null;
        $statusValid = in_array($statusLama, ['hadir', 'telat', 'izin', 'alpha'], true) ? $statusLama : 'hadir';

        // Simpan absensi
        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $tanggal],
            [
                'check_in_time'  => $tipe === 'masuk' ? $jam : ($existingAttendance->check_in_time ?? null),
                'check_out_time' => $tipe === 'pulang' ? $jam : ($existingAttendance->check_out_time ?? null),
                'jam_masuk'      => $statusHari['check_in'] ?: ($existingAttendance->jam_masuk ?? null),
                'jam_pulang'     => $statusHari['check_out'] ?: ($existingAttendance->jam_pulang ?? null),
                'status'         => $tipe === 'masuk' ? ($isLate ? 'telat' : 'hadir') : $statusValid,
                'is_late'        => $tipe === 'masuk' ? $isLate : (int) ($existingAttendance->is_late ?? 0),
                'selisih_menit'  => $tipe === 'masuk' ? $selisihMenit : (int) ($existingAttendance->selisih_menit ?? 0),
            ]
        );
    
        // Format info keterlambatan
        $tanggalFormat = $jamSekarang->translatedFormat('l, j F Y');
        $formatSelisih = '';
        if ($selisihMenit > 0) {
            $jamTerlambat = floor($selisihMenit / 60);
            $menitSisa = $selisihMenit % 60;
            if ($jamTerlambat > 0) {
                $formatSelisih .= $jamTerlambat . ' Jam ';
            }
            if ($menitSisa > 0) {
                $formatSelisih .= $menitSisa . ' Menit';
            }
        }
    
        return response()->json([
            'message' => 'Absensi berhasil dicatat.',
            'jam' => $jam,
            'tanggal' => $tanggalFormat,
            'status' => $isLate,
            'terlambat' => $formatSelisih,
            'kondisi' => 'Absensi'
        ]);
    }

    public function updateManual(Request $request, $id)
    {
        $request->validate([
            'is_late' => 'required|boolean',
            'selisih_menit' => 'required|integer|min:0',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s',
        ]);
    
        $attendance = Attendance::findOrFail($id);
        $attendance->is_late = $request->is_late;
        $attendance->selisih_menit = $request->selisih_menit;
        
        // Update check_in_time jika diisi
        if ($request->filled('check_in_time')) {
            $attendance->check_in_time = $request->check_in_time;
        }
        
        // Update check_out_time jika diisi
        if ($request->filled('check_out_time')) {
            $attendance->check_out_time = $request->check_out_time;
        }
        
        $attendance->save();
    
        return redirect()->back()->with('success', 'Data absensi berhasil diperbarui.');
    }




    public function getAbsensiHariIni(Request $request)
    {
        // Ambil dari user yang sedang login; admin boleh memakai ?user_id=
        $user = $request->user();
        $userId = ($user && $user->role === 'admin' && $request->filled('user_id'))
            ? $request->query('user_id')
            : optional($user)->id;

        if (!$userId) {
            return response()->json(['message' => 'Tidak ada user yang terautentikasi.'], 401);
        }

        $today = Carbon::today('Asia/Makassar')->format('Y-m-d');

        $attendance = Attendance::where('user_id', $userId)
                        ->whereDate('date', $today)
                        ->first();

        if ($attendance) {
            return response()->json([
                'user_id'        => $attendance->user_id,
                'date'           => $attendance->date,
                'check_in_time'  => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
            ]);
        } else {
            return response()->json([
                'message' => 'Absensi tidak ditemukan untuk hari ini.',
            ], 404);
        }
    }

    public function jadwal()
    {
        $jadwal = JamAbsensi::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Data jadwal berhasil diambil',
            'data' => $jadwal
        ], 200);
    }
    public function lokasiAbsennya()
    {
        $lokasi = Location::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Data jadwal berhasil diambil',
            'data' => $lokasi
        ], 200);
    }

    public function indexAbsensi(Request $request, $id)
    {
        // Pegawai hanya boleh melihat riwayat absensinya sendiri.
        $user = $request->user();
        if ($user && $user->role !== 'admin' && (string) $user->id !== (string) $id) {
            abort(403, 'Anda tidak berhak melihat riwayat absensi pegawai lain.');
        }

        if ($request->filled('date_range')) {
            $range = array_map('trim', explode(' to ', $request->date_range));

            if (count($range) < 2) {
                return redirect()->back()->with('error', 'Format rentang tanggal tidak valid.');
            }

            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $range[0])->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $range[1])->endOfDay();
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Format tanggal tidak valid.');
            }
        } else {
            // Default: awal dan akhir bulan ini
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        $absensi = Attendance::whereBetween('date', [$startDate, $endDate])
                    ->where('user_id', $id)
                    ->orderBy('id', 'desc')
                    ->get();

        return view('apps-list-kehadiran', compact('absensi'));
    }
    public function indexGaji($id)
    {
        // $bulan = Carbon::now()->format('M, Y');
        $kitirbulanan = KitirGaji::where('user_id', $id);
        $kehadiran = Attendance::where('user_id', $id)->latest()->first();
        return view('apps-invoices-user', compact('kitirbulanan'));
    }
    public function indexHome($id)
    {
        $bulan = Carbon::now()->format('yyyy-mm-dd');
        $kehadiran = Attendance::where('user_id', $id)->where('date', $bulan)->latest()->first();
        return view('apps-home', compact('kehadiran'));
    }


    public function absenMasuk(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $errorRadius = $this->periksaRadius($request->latitude, $request->longitude);
        if ($errorRadius) {
            return response()->json(['message' => $errorRadius], 422);
        }

        $userId = $request->user()->id;
        $today = Carbon::today('Asia/Makassar')->toDateString(); // WITA (GMT+8)
        $now = Carbon::now('Asia/Makassar')->format('H:i:s'); // Format jam HH:mm:ss

        // Cek status hari (libur nasional / libur sekolah / pengaturan tanggal / jadwal harian)
        $statusHari = HariLibur::status($today);

        if ($statusHari['libur']) {
            return response()->json([
                'message' => 'Absensi tidak aktif pada tanggal ini.' . (!empty($statusHari['keterangan']) ? ' Keterangan: ' . $statusHari['keterangan'] : '')
            ], 422);
        }

        if (empty($statusHari['check_in'])) {
            return response()->json(['message' => 'Jam kerja untuk hari ini tidak ditemukan'], 404);
        }

        $attendance = Attendance::firstOrCreate([
            'user_id' => $userId,
            'date' => $today
        ]);

        if (!empty($attendance->check_in_time)) {
            return response()->json(['message' => 'Anda sudah absen masuk hari ini.'], 400);
        }

        $scheduledCheckIn = Carbon::createFromFormat('H:i:s', $statusHari['check_in'], 'Asia/Makassar');

        $isLate = Carbon::parse($now, 'Asia/Makassar')->gt($scheduledCheckIn);
        $selisihMenit = $isLate ? (int) $scheduledCheckIn->diffInMinutes($now) : 0;

        $attendance->update([
            'check_in_time' => $now, // Sudah format HH:mm:ss
            'latitude_masuk' => $request->latitude,
            'longitude_masuk' => $request->longitude,
            'status' => $isLate ? 'telat' : 'hadir',
            'is_late' => $isLate ? 1 : 0,
            'selisih_menit' => $selisihMenit
        ]);

        return response()->json(['message' => 'Absen masuk berhasil']);
    }

    public function absenPulang(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $errorRadius = $this->periksaRadius($request->latitude, $request->longitude);
        if ($errorRadius) {
            return response()->json(['message' => $errorRadius], 422);
        }

        $userId = $request->user()->id;
        $today = Carbon::today('Asia/Makassar')->toDateString();
        $now = Carbon::now('Asia/Makassar')->format('H:i:s');

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if (!$attendance || empty($attendance->check_in_time)) {
            return response()->json(['message' => 'Belum absen masuk'], 400);
        }

        if (!empty($attendance->check_out_time)) {
            return response()->json(['message' => 'Anda sudah absen pulang hari ini.'], 400);
        }

        $attendance->update([
            'check_out_time' => $now,
            'latitude_pulang' => $request->latitude,
            'longitude_pulang' => $request->longitude
        ]);

        return response()->json(['message' => 'Absen pulang berhasil']);
    }

    public function riwayat(Request $request)
    {
        $attendance = $request->user()->attendances()->latest()->paginate(10);

        return response()->json($attendance);
    }

    public function detail(Request $request, $date)
    {
        // Hanya absensi milik user yang sedang login (admin boleh semua).
        $query = Attendance::where('date', $date);
        $user = $request->user();

        if ($user && $user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->first());
    }

    // 
    public function index()
    {
        $attendances = Attendance::with('user')->latest()->get();
        return view('attendance.index', compact('attendances'));
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
    
    public function storeManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s',
            'is_late' => 'boolean',
            'selisih_menit' => 'integer|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);

        // Check if attendance already exists for this user on this date
        $existingAttendance = Attendance::where('user_id', $request->user_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existingAttendance) {
            return redirect()->back()
                ->with('error', 'Data absensi untuk karyawan ini pada tanggal tersebut sudah ada!')
                ->withInput();
        }

        // Prepare data
        $data = [
            'user_id' => $request->user_id,
            'date' => $request->date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'is_late' => $request->is_late ?? false,
            'selisih_menit' => $request->selisih_menit ?? 0,
            'keterangan' => $request->keterangan,
        ];

        // Calculate working hours if both check in and out are provided
        if ($request->check_in_time && $request->check_out_time) {
            $checkIn = Carbon::parse($request->date . ' ' . $request->check_in_time);
            $checkOut = Carbon::parse($request->date . ' ' . $request->check_out_time);
            
            // Handle if check out is on the next day
            if ($checkOut < $checkIn) {
                $checkOut->addDay();
            }
            
            $workingHours = $checkOut->diffInHours($checkIn) + ($checkOut->diffInMinutes($checkIn) % 60) / 60;
            $data['total_jam_kerja'] = round($workingHours, 2);
            
            // Calculate overtime if needed (more than 8 hours)
            if ($workingHours > 8) {
                $data['lembur'] = $workingHours - 8;
            }
        }

        // Create attendance
        $attendance = Attendance::create($data);

        return redirect()->back()
            ->with('success', 'Data absensi berhasil ditambahkan secara manual.');
    }

    public function edit(Attendance $attendance)
    {
        $users = User::where('role', '!=', 'admin')->get();
        return view('attendance.edit', compact('attendance', 'users'));
    }

    public function update(Request $request, Attendance $attendance)
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

        $attendance->update($request->all());

        return redirect()->route('attendance.index')->with('success', 'Data kehadiran berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index')->with('success', 'Data kehadiran berhasil dihapus.');
    }
    
    public function destroyAbsenku($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return redirect()->back()
                ->with('success', 'Data absensi berhasil dihapus.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}

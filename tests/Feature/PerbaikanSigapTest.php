<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\JamAbsensi;
use App\Support\HariLibur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PerbaikanSigapTest extends TestCase
{
    use DatabaseTransactions;

    private function pegawai(): User
    {
        return User::where('role', 'pegawai')->firstOrFail();
    }

    private function pegawaiLain(): User
    {
        return User::where('role', 'pegawai')->where('id', '!=', $this->pegawai()->id)->firstOrFail();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    /**
     * Titik koordinat dekat lokasi sekolah (radius 100 m).
     */
    private function koordinatSekolah(): array
    {
        return ['latitude' => '-8.5690511', 'longitude' => '116.0968786'];
    }

    public function test_pegawai_tidak_bisa_membuka_slip_gaji_orang_lain(): void
    {
        $this->actingAs($this->pegawai())
            ->get('/slip-gaji/' . $this->pegawaiLain()->id)
            ->assertForbidden();
    }

    public function test_pegawai_tidak_bisa_membuka_profil_orang_lain(): void
    {
        $this->actingAs($this->pegawai())
            ->get('/profil-ptk/' . $this->pegawaiLain()->id)
            ->assertForbidden();
    }

    public function test_pegawai_tidak_bisa_membuka_halaman_admin(): void
    {
        // Middleware role mengalihkan pegawai ke halaman absensinya.
        $this->actingAs($this->pegawai())
            ->get('/admin-data-ptk')
            ->assertRedirect();
    }

    public function test_pegawai_tidak_bisa_mengganti_password_orang_lain(): void
    {
        $target = $this->pegawaiLain();

        $this->actingAs($this->pegawai())
            ->post('/update-password/' . $target->id, [
                'current_password' => 'apa-saja',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertForbidden();
    }

    public function test_operator_hanya_bisa_membuka_pengaturan_absensi(): void
    {
        if (!User::where('role', 'operator')->exists()) {
            $this->markTestSkipped('Belum ada akun operator di DB uji.');
        }

        $operator = User::where('role', 'operator')->firstOrFail();

        $this->actingAs($operator)
            ->get('/admin-setting-absensi')
            ->assertOk();

        $this->actingAs($operator)
            ->get('/admin-data-ptk')
            ->assertRedirect();

        $this->actingAs($operator)
            ->get('/admin-kitir-gajis')
            ->assertRedirect();
    }

    public function test_api_absen_tanpa_token_ditolak(): void
    {
        $this->postJson('/api/absen/masuk', [
            'latitude' => '-8.5',
            'longitude' => '116.1',
            'user_id' => $this->pegawai()->id,
        ])->assertUnauthorized();
    }

    public function test_api_absen_memakai_user_dari_token_bukan_body(): void
    {
        $pegawai = $this->pegawai();
        $lain = $this->pegawaiLain();
        $tanggal = now('Asia/Makassar')->toDateString();

        // Bersihkan dulu (transaksi akan di-rollback setelah test).
        Attendance::whereIn('user_id', [$pegawai->id, $lain->id])
            ->whereDate('date', $tanggal)
            ->delete();

        $token = $pegawai->createToken('uji')->plainTextToken;

        $this->withToken($token)->postJson('/api/absen/masuk', array_merge(
            $this->koordinatSekolah(),
            ['user_id' => $lain->id]
        ));

        // Absensi tidak boleh tercatat atas nama user lain.
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $lain->id,
            'date' => $tanggal,
        ]);
    }

    public function test_api_absen_di_luar_radius_ditolak(): void
    {
        $pegawai = $this->pegawai();
        $tanggal = now('Asia/Makassar')->toDateString();

        Attendance::where('user_id', $pegawai->id)->whereDate('date', $tanggal)->delete();

        $token = $pegawai->createToken('uji-radius')->plainTextToken;

        // Titik jauh dari lokasi sekolah (radius 100 m).
        $this->withToken($token)->postJson('/api/absen/masuk', [
            'latitude' => '0.5',
            'longitude' => '0.5',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $pegawai->id,
            'date' => $tanggal,
        ]);
    }

    public function test_halaman_absen_pegawai_tampil_normal(): void
    {
        $pegawai = $this->pegawai();

        $this->actingAs($pegawai)
            ->get('/mari-absensi-hari-ini/' . $pegawai->id)
            ->assertOk();
    }

    public function test_halaman_slip_gaji_tanpa_arsip_sama_sekali_tidak_error(): void
    {
        $user = User::create([
            'id' => '9999999999999',
            'name' => 'Pegawai Tanpa Arsip',
            'email' => 'tanpa-arsip@uji.test',
            'password' => bcrypt('rahasia123'),
            'role' => 'pegawai',
            'avatar' => 'avatar-1.jpg',
        ]);

        $this->actingAs($user)
            ->get('/slip-gaji/' . $user->id)
            ->assertOk();
    }

    public function test_halaman_slip_gaji_bulan_kosong_tidak_error(): void
    {
        $this->actingAs($this->pegawai())
            ->get('/slip-gaji/' . $this->pegawai()->id . '?bulan=Jan, 2001')
            ->assertOk();
    }

    public function test_admin_bisa_membuka_halaman_kitir(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin-kitir-gajis')
            ->assertOk();
    }

    public function test_absensi_ditolak_pada_hari_libur(): void
    {
        $pegawai = $this->pegawai();
        $tanggal = now('Asia/Makassar')->toDateString();

        Attendance::where('user_id', $pegawai->id)->whereDate('date', $tanggal)->delete();

        // Tandai hari ini sebagai libur nasional.
        JamAbsensi::create([
            'hari' => $tanggal,
            'check_in' => '00:00:00',
            'check_out' => '00:00:00',
            'status' => 'Nonactive',
            'is_periode_libur' => true,
            'tanggal_mulai' => $tanggal,
            'tanggal_selesai' => $tanggal,
            'keterangan_libur' => 'Libur Nasional: Uji Coba',
        ]);

        $token = $pegawai->createToken('uji-libur')->plainTextToken;

        $this->withToken($token)->postJson('/api/absen/masuk', $this->koordinatSekolah())
            ->assertStatus(422);

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $pegawai->id,
            'date' => $tanggal,
        ]);
    }

    public function test_tambah_libur_tanggal_lewat_membuat_penanda_dan_tidak_alfa(): void
    {
        $admin = $this->admin();

        // Tanggal jauh di masa lalu supaya semua pegawai belum punya baris absensi.
        $tanggal = '2001-01-02';

        $this->actingAs($admin)->post('/admin/periode-libur/store', [
            'jenis' => 'sekolah',
            'tanggal_mulai' => $tanggal,
            'tanggal_selesai' => $tanggal,
            'keterangan_libur' => 'Libur Semester Uji',
        ])->assertRedirect();

        $this->assertDatabaseHas('jam_absensi', [
            'tanggal_mulai' => $tanggal,
            'is_periode_libur' => true,
            'keterangan_libur' => 'Libur Sekolah: Libur Semester Uji',
        ]);

        // Semua pegawai mendapat penanda libur (status "-"), bukan alfa.
        $this->assertDatabaseHas('attendances', [
            'date' => $tanggal,
            'status' => '-',
        ]);

        $this->assertTrue(HariLibur::status($tanggal)['libur']);
        $this->assertContains($tanggal, HariLibur::daftar($tanggal, $tanggal));
    }

    public function test_generate_libur_nasional(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/periode-libur/nasional', ['tahun' => 2025])
            ->assertRedirect();

        $this->assertDatabaseHas('jam_absensi', [
            'tanggal_mulai' => '2025-01-01',
            'is_periode_libur' => true,
            'keterangan_libur' => 'Libur Nasional: Tahun Baru 2025',
        ]);

        $this->assertTrue(HariLibur::status('2025-01-01')['libur']);
    }

    public function test_libur_pada_periode_terpublish_memberi_peringatan(): void
    {
        $admin = $this->admin();

        // Sep, 2026 sudah ada di arsip (data uji), jadi harus memunculkan peringatan.
        $this->actingAs($admin)->post('/admin/periode-libur/store', [
            'jenis' => 'nasional',
            'tanggal_mulai' => '2026-09-02',
            'tanggal_selesai' => '2026-09-02',
            'keterangan_libur' => 'Perbaikan Libur Terlewat',
        ])->assertSessionHas('warning');

        $this->assertDatabaseHas('jam_absensi', [
            'tanggal_mulai' => '2026-09-02',
            'keterangan_libur' => 'Libur Nasional: Perbaikan Libur Terlewat',
        ]);
    }
}

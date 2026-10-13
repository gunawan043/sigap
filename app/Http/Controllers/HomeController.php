<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Attendance; // Sesuaikan nama modelmu
use App\Models\Absensi;    // Model untuk konstanta/setting
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $path = $request->path();
        $role = $user->role ?? null;

        // Halaman admin (& operator) tidak boleh dibuka pegawai lewat catch-all.
        if ($role === 'pegawai' && !str_starts_with($path, 'apps-')) {
            abort(403, 'Anda tidak berhak membuka halaman ini.');
        }

        if ($role === 'operator' && $path !== 'admin-setting-absensi') {
            abort(403, 'Anda tidak berhak membuka halaman ini.');
        }

        if (view()->exists($path)) {
            return view($path);
        }
        return abort(404);
    }

    public function root()
{
    $setting = Absensi::first();

    // if (!$setting) {
    //     return redirect()->back()->with('error', 'Data Master Absensi (Setting) belum diisi!');
    // }

    // $val_toleransi = floor($setting->toleransi_keterlambatan / 4);
    // $val_potongan  = floor($setting->pot_absen);

    // $attendances = Attendance::all();

    // foreach ($attendances as $att) {

    //     $selisihMenit = 0;
    //     $isLate = 0;

    //     // 🔥 0️⃣ DATA KOSONG (DARI GENERATE / LIBUR / TIDAK DIHITUNG)
    //     if (is_null($att->check_in_time) && is_null($att->check_out_time)) {

    //         $selisihMenit = 0;
    //         $isLate = 0;

    //     }
    //     // 1️⃣ Tidak absen masuk (TAPI ADA DATA PULANG → ANEH / INVALID)
    //     elseif (is_null($att->check_in_time)) {

    //         $selisihMenit = $val_toleransi;
    //         $isLate = 1;

    //     }
    //     // 2️⃣ Masuk tapi tidak absen pulang
    //     elseif (!is_null($att->check_in_time) && is_null($att->check_out_time)) {

    //         $selisihMenit = $val_potongan;

    //         if ($att->jam_masuk && $att->check_in_time > $att->jam_masuk) {
    //             $isLate = 1;
    //         }

    //     }
    //     // 3️⃣ Normal cek keterlambatan
    //     else {

    //         if ($att->jam_masuk && $att->check_in_time > $att->jam_masuk) {

    //             $jamMasuk = Carbon::parse($att->jam_masuk);
    //             $checkIn  = Carbon::parse($att->check_in_time);

    //             $diffDetik = $jamMasuk->diffInSeconds($checkIn);
    //             $selisihMenit = floor($diffDetik / 60);

    //             $isLate = 1;

    //         } else {

    //             $selisihMenit = 0;
    //             $isLate = 0;

    //         }
    //     }

    //     $att->update([
    //         'selisih_menit' => $selisihMenit,
    //         'is_late'       => $isLate
    //     ]);
    // }

    return view('admin-dashboard');
}

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        // Pegawai hanya boleh mengubah profilnya sendiri.
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak mengubah profil pegawai lain.');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "User Details Updated successfully!"
            // ], 200); // Status code here
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "Something went wrong!"
            // ], 200); // Status code here
            return redirect()->back();

        }
    }

    public function updatePassword(Request $request, $id)
    {
        // Pegawai hanya boleh mengganti password miliknya sendiri.
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak mengganti password pegawai lain.');

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = Auth::user();
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }
}

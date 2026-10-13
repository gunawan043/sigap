<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\NotificationToken;

class AuthController extends Controller
{
    /**
     * Tujuan redirect setelah login berdasarkan role.
     */
    private function tujuanLogin(User $user): ?string
    {
        return match ($user->role) {
            'admin'    => '/',
            'pegawai'  => '/mari-absensi-hari-ini/' . $user->id,
            'operator' => '/admin-setting-absensi',
            default    => null,
        };
    }

    public function logindah(Request $request)
    {
        if (Auth::check()) {
            $tujuan = $this->tujuanLogin(Auth::user());

            return $tujuan
                ? redirect()->to($tujuan)
                : (Auth::logout() ? back()->withErrors(['email' => 'Role tidak dikenal.']) : back());
        }

        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            $tujuan = $this->tujuanLogin($user);
            if ($tujuan) {
                return redirect()->to($tujuan);
            }

            Auth::logout();
            return back()->withErrors(['email' => 'Role tidak dikenal.']);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
            'fcm_token' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        if ($request->filled('fcm_token')) {
            NotificationToken::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => $request->fcm_token]
            );
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KitirGaji;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function updateProfileAppsAdmin(Request $request, $id)
    {
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak mengubah profil admin lain.');

        $user = User::findOrFail($id);

        // Update basic info
        $user->alamat = $request->input('alamat');
        $user->no_hp = $request->input('no_hp');

        // Update avatar jika ada
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('build/images/users'), $filename);
            $user->avatar = $filename;
        }

        $user->save();

        return redirect()->to('/profil-admin/' . $user->id)->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePasswordAdmin(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'current_password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak cocok.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->to('/profil-admin/' . $user->id)->with('success', 'Password berhasil diperbarui.');
    }

    public function indexProfilAdmin($id)
    {
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak melihat profil admin lain.');

        $users = User::where('id', $id)->first();
        return view('admin-pages-profile', compact('users'));
    }
    public function editProfilAdmin($id)
    {
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak mengubah profil admin lain.');

        $users = User::where('id', $id)->first();
        return view('admin-edit-pegawai', compact('users'));
    }

    /**
     * Update profil admin yang sedang login
     * (dipakai route /admin-update/data-admin-aku/{id}).
     */
    public function updateProfileAdmin(Request $request, $id)
    {
        return $this->updateProfileAppsAdmin($request, $id);
    }


    /////////////////////////


    public function index()
    {
        // Jangan tampilkan user yang role-nya admin
        $users = User::where('role', '!=', 'admin')->get();
        return view('pegawai.index', compact('users'));
    }

    public function updateProfileApps(Request $request, $id)
    {
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak mengubah profil pegawai lain.');

        $user = User::findOrFail($id);

        // Update basic info
        $user->alamat = $request->input('alamat');
        $user->no_hp = $request->input('no_hp');

        // Update avatar jika ada
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('build/images/users'), $filename);
            $user->avatar = $filename;
        }

        $user->save();

        return redirect()->to('/profil-ptk/' . $user->id)->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'current_password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak cocok.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->to('/profil-ptk/' . $user->id)->with('success', 'Password berhasil diperbarui.');
    }

    public function indexProfil($id)
    {
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak melihat profil pegawai lain.');

        $users = User::where('id', $id)->first();
        return view('apps-pages-profile', compact('users'));
    }
    public function editProfil($id)
    {
        abort_unless((string) Auth::id() === (string) $id, 403, 'Anda tidak berhak mengubah profil pegawai lain.');

        $users = User::where('id', $id)->first();
        return view('apps-edit-pegawai', compact('users'));
    }

    public function create()
    {
        return view('pegawai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'awal_masuk' => 'required|string', // cukup string, nanti parsing manual
            'tanggal_lahir' => 'required|string',
            'tempat_lahir' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048', // file cek tetap boleh
        ]);
        $tanggalAwalMasuk = \Carbon\Carbon::createFromFormat('d M, Y', $request->awal_masuk);
        $tanggalLahir = \Carbon\Carbon::createFromFormat('d M, Y', $request->tanggal_lahir);

        $awalMasukFormat = $tanggalAwalMasuk->format('Y'); // TahunBulanTanggal
        $TTLFormat = $tanggalLahir->format('Ym');             // BulanTanggal
        $angkaUnik = str_pad(rand(0, 999), 2, '0', STR_PAD_LEFT);

        // Custom ID
        $customId = $awalMasukFormat . $TTLFormat . $angkaUnik;
        $avatarName = 'default.svg'; 

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
        }

        User::create([
            'id' => $customId,
            'name' => $request->name,
            'email' => $customId,
            'no_hp' => $request->no_hp,
            'jk' => $request->jk,
            'alamat' => $request->alamat,
            'induk' => $request->induk,
            'ttl' => $request->tempat_lahir .' ' . $request->tanggal_lahir,
            'subjek' => $request->subjek,
            'awal_masuk' => $request->awal_masuk,
            'pendidikan' => $request->pendidikan,
            'avatar' =>  $avatarName,
            'role' => 'pegawai',
            'password' => Hash::make($customId),
            'dana_bos' => $request->dana_bos,
            'sertifikasi' => $request->sertifikasi,
        ]);
        KitirGaji::create([
            'user_id' => $customId,
            'nama_ptk' => $request->name,
            'induk' => $request->induk,
            'tmt' => $request->awal_masuk,
            'jk' => $request->jk,
        ]);

        return back()->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit(User $pegawai)
    {
        return view('pegawai.edit', compact('pegawai'));
    }
    public function get()
    {
        return view('admin-data-pegawai');
    }

    public function update(Request $request, $id)
    {
        User::where('id', $id)->update([
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'jk' => $request->jk,
            'alamat' => $request->alamat,
            'subjek' => $request->subjek,
            'awal_masuk' => $request->awal_masuk,
            'pendidikan' => $request->pendidikan,
            'induk' => $request->induk,
            'dana_bos' => $request->dana_bos,
            'sertifikasi' => $request->sertifikasi,
        ]);

        return back()->with('success', 'Data pegawai berhasil diupdate.');
    }

    public function destroy($id)
    {
        $pegawai = User::findOrFail($id);
        $pegawai->delete();
        $kitir = KitirGaji::where('user_id', $id);
        $kitir->delete();

        return back()->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah'], 400);
        }

        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->update($request->only('name', 'no_hp', 'alamat'));

        return response()->json(['message' => 'Profil berhasil diperbarui']);
    }


}

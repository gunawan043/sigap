<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationToken;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationController extends Controller
{
    /**
     * Kirim notifikasi ke perangkat milik user yang sedang login.
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'user_id' => 'nullable',
        ]);

        $user = $request->user();
        $targetUserId = ($user->role === 'admin' && $request->filled('user_id'))
            ? $request->user_id
            : $user->id;

        $token = NotificationToken::where('user_id', $targetUserId)->first();

        if (!$token) {
            return response()->json(['message' => 'Token notifikasi tidak ditemukan.'], 404);
        }

        $serviceAccount = storage_path('app/firebase/firebase-adminsdk.json');

        if (!is_file($serviceAccount)) {
            return response()->json(['message' => 'Firebase belum dikonfigurasi di server.'], 503);
        }

        try {
            $messaging = (new Factory)
                ->withServiceAccount($serviceAccount)
                ->createMessaging();

            $messaging->send(
                CloudMessage::withTarget('token', $token->token)
                    ->withNotification(Notification::create($request->title, $request->body))
            );

            return response()->json(['message' => 'Notifikasi berhasil dikirim.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal mengirim notifikasi.'], 500);
        }
    }
}

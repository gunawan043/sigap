<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FcmService
{
    protected $serverKey;

    public function __construct()
    {
        $this->serverKey = env('FCM_SERVER_KEY');
    }

    public function sendNotification($token, $title, $body)
    {
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ],
            'priority' => 'high'
        ]);

        return $response->json();
    }
}

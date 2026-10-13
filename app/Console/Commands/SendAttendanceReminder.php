<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\NotificationToken;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Carbon\Carbon;

class SendAttendanceReminder extends Command
{
    protected $signature = 'reminder:attendance';
    protected $description = 'Kirim notifikasi untuk mengingatkan absen check-in dan check-out';

    public function handle()
    {
        $serviceAccount = storage_path('app/firebase/firebase-adminsdk.json');

        if (!is_file($serviceAccount)) {
            $this->warn('Firebase service account tidak ditemukan, reminder dilewati: ' . $serviceAccount);
            return Command::SUCCESS;
        }

        $messaging = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->createMessaging();

        $now = Carbon::now('Asia/Makassar');
        $jam = $now->format('H:i');

        // Misal jam kerja normal 08:00 check-in, 16:00 check-out
        $checkInTime = '08:00';
        $checkOutTime = '16:00';

        // Cari pegawai yang belum memiliki data absensi hari ini
        $users = User::where('role', 'pegawai')
            ->whereDoesntHave('attendances', function ($query) use ($now) {
                $query->whereDate('date', $now->toDateString());
            })
            ->get();

        foreach ($users as $user) {
            $tokenRecord = NotificationToken::where('user_id', $user->id)->first();
            if (!$tokenRecord) {
                continue; // tidak ada token, skip
            }

            // Logic reminder 5 menit sebelum/sesudah
            $shouldRemind = false;
            $messageTitle = '';
            $messageBody = '';

            if ($this->isWithinFiveMinutes($jam, $checkInTime)) {
                $shouldRemind = true;
                $messageTitle = 'Reminder Absen Masuk';
                $messageBody = 'Jangan lupa absen masuk!';
            } elseif ($this->isWithinFiveMinutes($jam, $checkOutTime)) {
                $shouldRemind = true;
                $messageTitle = 'Reminder Absen Pulang';
                $messageBody = 'Jangan lupa absen pulang!';
            }

            if ($shouldRemind) {
                $this->sendPushNotification($messaging, $tokenRecord->token, $messageTitle, $messageBody);
            }
        }

        return Command::SUCCESS;
    }

    private function isWithinFiveMinutes($currentTime, $targetTime)
    {
        $current = Carbon::createFromFormat('H:i', $currentTime);
        $target = Carbon::createFromFormat('H:i', $targetTime);

        return abs($current->diffInMinutes($target)) <= 5;
    }

    private function sendPushNotification($messaging, $deviceToken, $title, $body)
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification);

            $messaging->send($message);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

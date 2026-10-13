<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;

class AttendanceLogController extends Controller
{
    public function index()
    {
        $logs = AttendanceLog::with('user')->latest()->paginate(20);
        return view('attendance_logs.index', compact('logs'));
    }
}

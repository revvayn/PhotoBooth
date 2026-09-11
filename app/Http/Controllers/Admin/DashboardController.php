<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Frame;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSessionsToday = ActivityLog::where('created_at', '>=', Carbon::today())
            ->distinct('session_id')
            ->count('session_id');

        $totalPhotos = ActivityLog::where('action', 'foto.taken')
            ->count();

        $totalFrames = Frame::count();
        $totalLogs = ActivityLog::count();

        $recentLogs = ActivityLog::orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.dashboard', compact(
            'totalSessionsToday',
            'totalPhotos',
            'totalFrames',
            'totalLogs',
            'recentLogs'
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Frame;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private const DAYS = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    public function index()
    {
        // Log hanya berisi pembayaran, jadi seluruh statistik dihitung dari sini.
        $payments = ActivityLog::payments()->orderBy('created_at', 'desc')->get();

        $today = Carbon::today();
        $todayPayments = $payments->filter(fn ($p) => $p->created_at->gte($today));

        $sum = fn ($rows) => (int) $rows->sum(fn ($p) => $p->payment_total);
        $byMethod = fn ($rows, string $m) => $rows->filter(fn ($p) => $p->payment_method === $m);

        $stats = [
            'revenue_today' => $sum($todayPayments),
            'count_today' => $todayPayments->count(),
            'revenue_total' => $sum($payments),
            'count_total' => $payments->count(),
            'qris_total' => $sum($byMethod($payments, 'qris')),
            'qris_count' => $byMethod($payments, 'qris')->count(),
            'cash_total' => $sum($byMethod($payments, 'cash')),
            'cash_count' => $byMethod($payments, 'cash')->count(),
            'photos_total' => (int) $payments->sum(fn ($p) => $p->payment_photo_count),
        ];

        // Pendapatan 7 hari terakhir (termasuk hari ini).
        $week = collect(range(6, 0))->map(function (int $ago) use ($payments, $sum) {
            $day = Carbon::today()->subDays($ago);
            $rows = $payments->filter(fn ($p) => $p->created_at->isSameDay($day));

            return [
                'label' => self::DAYS[$day->dayOfWeek],
                'date' => $day->format('d/m'),
                'revenue' => $sum($rows),
                'count' => $rows->count(),
                'is_today' => $ago === 0,
            ];
        });

        return view('admin.dashboard', [
            'stats' => $stats,
            'week' => $week,
            'weekMax' => max(1, (int) $week->max('revenue')),
            'framesActive' => Frame::active()->count(),
            'framesTotal' => Frame::count(),
            'recent' => $payments->take(8),
        ]);
    }
}

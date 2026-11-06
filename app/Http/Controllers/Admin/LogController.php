<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Log pembayaran: hanya transaksi yang dikonfirmasi di kiosk.
 * Penyaringan, pengurutan, dan total dihitung di PHP supaya
 * konsisten di MySQL maupun SQLite (data ada di kolom JSON).
 */
class LogController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $payments = $this->payments($filters);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $logs = new LengthAwarePaginator(
            $payments->forPage($page, self::PER_PAGE)->values(),
            $payments->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $sum = fn ($rows) => (int) $rows->sum(fn ($p) => $p->payment_total);

        return view('admin.logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'summary' => [
                'count' => $payments->count(),
                'revenue' => $sum($payments),
                'qris' => $sum($payments->filter(fn ($p) => $p->payment_method === 'qris')),
                'cash' => $sum($payments->filter(fn ($p) => $p->payment_method === 'cash')),
                'voucher' => $sum($payments->filter(fn ($p) => $p->payment_method === 'voucher')),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $payments = $this->payments($this->filters($request));
        $name = 'pembayaran-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($payments) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Waktu', 'Antrian', 'Metode', 'Frame', 'Jumlah Foto', 'Total (Rp)']);
            foreach ($payments as $p) {
                fputcsv($out, [
                    $p->created_at->format('Y-m-d H:i:s'),
                    $p->queue_number,
                    strtoupper($p->payment_method),
                    $p->payment_items_label,
                    $p->payment_photo_count,
                    $p->payment_total,
                ]);
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'metode' => ['nullable', 'in:qris,cash,voucher'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:created_at,queue_number,metode,total'],
            'dir' => ['nullable', 'in:asc,desc'],
        ]);

        return [
            'q' => trim((string) ($data['q'] ?? '')),
            'metode' => $data['metode'] ?? '',
            'from' => $data['from'] ?? '',
            'to' => $data['to'] ?? '',
            'sort' => $data['sort'] ?? 'created_at',
            'dir' => $data['dir'] ?? 'desc',
        ];
    }

    private function payments(array $f)
    {
        $query = ActivityLog::payments();

        if ($f['from'] !== '') {
            $query->where('created_at', '>=', Carbon::parse($f['from'])->startOfDay());
        }
        if ($f['to'] !== '') {
            $query->where('created_at', '<=', Carbon::parse($f['to'])->endOfDay());
        }
        if ($f['q'] !== '') {
            $query->where('queue_number', 'like', '%'.$f['q'].'%');
        }

        $rows = $query->get();

        if ($f['metode'] !== '') {
            $rows = $rows->filter(fn ($p) => $p->payment_method === $f['metode']);
        }

        $key = match ($f['sort']) {
            'queue_number' => fn ($p) => (string) $p->queue_number,
            'metode' => fn ($p) => $p->payment_method,
            'total' => fn ($p) => $p->payment_total,
            default => fn ($p) => $p->created_at->timestamp,
        };

        return $rows->sortBy($key, SORT_REGULAR, $f['dir'] === 'desc')->values();
    }
}

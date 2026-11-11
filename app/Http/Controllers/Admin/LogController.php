<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\PaymentLogPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Log pembayaran: hanya transaksi yang dikonfirmasi di kiosk.
 * Penyaringan, pengurutan, dan total dihitung di PHP supaya
 * konsisten di MySQL maupun SQLite (data ada di kolom JSON).
 */
class LogController extends Controller
{
    private const PER_PAGE = 20;

    public function __construct(private readonly PaymentLogPresenter $presenter)
    {
    }

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

        $presented = $logs->getCollection()
            ->map(fn (ActivityLog $log) => $this->presenter->present($log))
            ->values();

        $logs->setCollection($presented);

        $sum = fn ($rows) => (int) $rows->sum(fn ($p) => $p->payment_total);

        return view('admin.logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'logData' => $presented->keyBy('id')->all(),
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

    /**
     * Unduh seluruh foto mentah + gambar frame milik satu transaksi.
     * Hasil komposit (foto + frame) bisa diunduh langsung dari modal detail.
     */
    public function download(ActivityLog $log)
    {
        abort_unless($log->action === ActivityLog::PAYMENT, 404);

        $data = $this->presenter->present($log);
        $tmp = tempnam(sys_get_temp_dir(), 'photobooth-log-');

        $zip = new ZipArchive();
        if ($tmp === false || $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->route('admin.logs.index')
                ->with('error', 'Gagal menyiapkan file unduhan.');
        }

        $added = 0;
        $queue = Str::slug($log->queue_number ?: 'antrian') ?: 'antrian';

        foreach ($data['groups'] as $groupIndex => $group) {
            $prefix = sprintf('%02d-%s', $groupIndex + 1, Str::slug($group['name']) ?: 'frame');

            foreach ($group['photos'] as $photoIndex => $url) {
                $path = $this->publicPathFromUrl($url);
                if (! $path) {
                    continue;
                }

                $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
                $zip->addFile(
                    Storage::disk('public')->path($path),
                    $queue.'-'.$prefix.'-foto-'.($photoIndex + 1).'.'.$extension
                );
                $added++;
            }

            $framePath = $this->publicPathFromUrl($group['frame_url']);
            if ($framePath) {
                $extension = pathinfo($framePath, PATHINFO_EXTENSION) ?: 'png';
                $zip->addFile(
                    Storage::disk('public')->path($framePath),
                    $queue.'-'.$prefix.'-frame.'.$extension
                );
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            @unlink($tmp);

            return redirect()->route('admin.logs.index')
                ->with('error', 'Foto untuk transaksi ini tidak tersedia.');
        }

        return response()
            ->download($tmp, $queue.'-'.$log->id.'.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    public function destroy(Request $request, ActivityLog $log)
    {
        abort_unless($log->action === ActivityLog::PAYMENT, 404);

        $this->deletePaymentLog($log);

        return redirect()
            ->route('admin.logs.index', $request->only(['q', 'metode', 'from', 'to', 'sort', 'dir', 'page']))
            ->with('success', 'Log pembayaran berhasil dihapus.');
    }

    public function destroyFiltered(Request $request)
    {
        $filters = $this->filters($request);

        // Jangan izinkan tombol tanpa filter menghapus seluruh riwayat.
        if ($filters['q'] === '' && $filters['metode'] === '' && $filters['from'] === '' && $filters['to'] === '') {
            return redirect()
                ->route('admin.logs.index', $request->only(['sort', 'dir', 'page']))
                ->with('error', 'Pilih minimal satu filter sebelum menghapus.');
        }

        $logs = $this->payments($filters);
        foreach ($logs as $log) {
            $this->deletePaymentLog($log);
        }

        return redirect()
            ->route('admin.logs.index', $request->only(['q', 'metode', 'from', 'to', 'sort', 'dir', 'page']))
            ->with('success', $logs->count().' log pembayaran sesuai filter berhasil dihapus.');
    }

    private function deletePaymentLog(ActivityLog $log): void
    {
        $archiveDirectory = $log->metadata['archive_dir'] ?? null;
        if (is_string($archiveDirectory) && str_starts_with($archiveDirectory, 'arsip/')) {
            Storage::disk('public')->deleteDirectory($archiveDirectory);
        }
        // Folder lama (sebelum nama arsip diacak) tetap dibersihkan.
        Storage::disk('public')->deleteDirectory('arsip/'.$log->id);
        $log->delete();
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

    private function publicPathFromUrl(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path)) {
            return null;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? $path : null;
    }
}

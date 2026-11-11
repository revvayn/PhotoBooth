<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Frame;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Menyiapkan data log pembayaran untuk halaman admin.
 *
 * Log baru menyimpan foto arsip + detail frame di metadata. Log lama hanya
 * menyimpan nama frame, jadi presenter ini mencoba memulihkan foto dari
 * folder sesi yang mungkin masih tersisa dan mengelompokkannya sesuai
 * photo_count tiap frame.
 */
class PaymentLogPresenter
{
    /** @var array<string, array<int, string>> */
    private array $sessionPhotoCache = [];

    private ?Collection $frames = null;

    /**
     * @return array<string, mixed>
     */
    public function present(ActivityLog $log): array
    {
        $meta = is_array($log->metadata) ? $log->metadata : [];
        $items = is_array($meta['items'] ?? null) ? $meta['items'] : [];

        $hasStoredPhotos = collect($items)->contains(
            fn ($item) => is_array($item) && ! empty($item['photos'])
        );

        $sessionPhotos = $hasStoredPhotos ? [] : $this->sessionPhotos((string) $log->session_id);
        $offset = 0;
        $groups = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $photos = array_values(array_filter(
                (array) ($item['photos'] ?? []),
                fn ($photo) => is_string($photo) && $photo !== ''
            ));

            if (! $photos && $sessionPhotos) {
                $count = (int) ($item['photo_count'] ?? 0);
                if ($count <= 0) {
                    $count = count($sessionPhotos) - $offset;
                }
                $photos = array_slice($sessionPhotos, $offset, max(0, $count));
            }

            if ($sessionPhotos) {
                $offset += count($photos);
            }

            $frame = $this->findFrame($item);
            $name = (string) ($item['name'] ?? $frame?->name ?? 'Frame');
            $filters = $this->normalizeFilters($item['filters'] ?? [], count($photos));

            $groups[] = [
                'slug' => (string) ($item['slug'] ?? $frame?->slug ?? ''),
                'name' => $name,
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
                'price' => (int) ($item['price'] ?? 0),
                'photo_count' => max(1, (int) ($item['photo_count'] ?? $frame?->photo_count ?? count($photos))),
                'photo_total' => count($photos),
                'frame_url' => $this->normalizeUrl($item['image_url'] ?? $frame?->image_url ?? null),
                'slots' => $this->normalizeSlots($item['slots'] ?? $frame?->slots ?? []),
                'filters' => $filters,
                'photos' => $photos,
            ];
        }

        // Log lama tanpa item tetap bisa menampilkan foto mentah bila folder sesi ada.
        if (! $groups && $sessionPhotos) {
            $groups[] = [
                'slug' => '',
                'name' => 'Foto Sesi',
                'qty' => 1,
                'price' => 0,
                'photo_count' => count($sessionPhotos),
                'photo_total' => count($sessionPhotos),
                'frame_url' => null,
                'slots' => [],
                'filters' => array_fill(0, count($sessionPhotos), 'asli'),
                'photos' => $sessionPhotos,
            ];
        }

        $photoTotal = collect($groups)->sum('photo_total');

        return [
            'id' => $log->id,
            'queue' => $log->queue_number ?: '-',
            'created_at' => $log->created_at,
            'time' => $log->created_at?->format('d M Y · H:i') ?? '-',
            'method' => $log->payment_method,
            'total' => $log->payment_total,
            'photo_count' => $log->payment_photo_count,
            'photo_total' => $photoTotal,
            'voucher_code' => $meta['voucher_code'] ?? null,
            'groups' => $groups,
            'photos' => collect($groups)->flatMap(fn ($group) => $group['photos'])->values()->all(),
            'has_photos' => $photoTotal > 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function findFrame(array $item): ?Frame
    {
        if ($this->frames === null) {
            $this->frames = Frame::all();
        }

        $slug = (string) ($item['slug'] ?? '');
        if ($slug !== '') {
            $frame = $this->frames->firstWhere('slug', $slug);
            if ($frame) {
                return $frame;
            }
        }

        $name = (string) ($item['name'] ?? '');

        return $name !== '' ? $this->frames->firstWhere('name', $name) : null;
    }

    /**
     * @return array<int, string>
     */
    private function sessionPhotos(string $sessionId): array
    {
        if (array_key_exists($sessionId, $this->sessionPhotoCache)) {
            return $this->sessionPhotoCache[$sessionId];
        }

        if ($sessionId === '') {
            return $this->sessionPhotoCache[$sessionId] = [];
        }

        $directory = 'photos/'.$sessionId;
        if (! Storage::disk('public')->exists($directory)) {
            return $this->sessionPhotoCache[$sessionId] = [];
        }

        $files = collect(Storage::disk('public')->files($directory))
            ->filter(fn ($file) => preg_match('/\.(?:jpe?g|png|webp)$/i', $file) === 1)
            ->sortBy(fn ($file) => basename($file), SORT_NATURAL)
            ->values();

        return $this->sessionPhotoCache[$sessionId] = $files
            ->map(fn ($file) => '/storage/'.$file)
            ->all();
    }

    /**
     * @param  mixed  $filters
     * @return array<int, string>
     */
    private function normalizeFilters($filters, int $photoCount): array
    {
        $result = is_array($filters)
            ? array_values(array_map(
                fn ($filter) => is_string($filter) && $filter !== '' ? $filter : 'asli',
                $filters
            ))
            : [];

        while (count($result) < $photoCount) {
            $result[] = 'asli';
        }

        return array_slice($result, 0, $photoCount);
    }

    /**
     * @param  mixed  $slots
     * @return array<int, array<string, float>>
     */
    private function normalizeSlots($slots): array
    {
        if (! is_array($slots)) {
            return [];
        }

        return collect($slots)
            ->filter(fn ($slot) => is_array($slot))
            ->map(fn ($slot) => [
                'x' => (float) ($slot['x'] ?? 0),
                'y' => (float) ($slot['y'] ?? 0),
                'w' => (float) ($slot['w'] ?? 0),
                'h' => (float) ($slot['h'] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function normalizeUrl($url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, 'http')) {
            return $url;
        }

        return '/storage/'.ltrim($url, '/');
    }
}

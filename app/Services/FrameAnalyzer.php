<?php

namespace App\Services;

use RuntimeException;

class FrameAnalyzer
{
    /**
     * Deteksi area transparan (lubang foto) pada PNG frame.
     * Mengembalikan daftar slot ternormalisasi: [{x, y, w, h}] dalam koordinat 0..1.
     *
     * @throws RuntimeException jika gambar bukan PNG yang valid atau tidak ada lubang.
     */
    public function analyzePng(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('Berkas frame tidak ditemukan.');
        }

        $src = @imagecreatefrompng($path);
        if ($src === false) {
            throw new RuntimeException('Berkas harus berupa PNG yang valid.');
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        if ($srcW < 16 || $srcH < 16) {
            imagedestroy($src);
            throw new RuntimeException('Ukuran PNG frame terlalu kecil.');
        }

        // Analisis: gunakan resolusi penuh bila wajar, downscale hanya bila sangat besar
        $grid = $this->prepareGrid($src, $srcW, $srcH);
        imagedestroy($src);

        $cols = imagesx($grid);
        $rows = imagesy($grid);

        $alphaThreshold = 110; // alpha >= ini dianggap lubang transparan

        $transparent = array_fill(0, $rows, array_fill(0, $cols, false));
        for ($y = 0; $y < $rows; $y++) {
            for ($x = 0; $x < $cols; $x++) {
                $rgb = imagecolorat($grid, $x, $y);
                $a = ($rgb >> 24) & 0x7F; // 0 = opaque, 127 = transparan penuh
                if ($a >= $alphaThreshold) {
                    $transparent[$y][$x] = true;
                }
            }
        }
        imagedestroy($grid);

        $components = $this->findComponents($transparent, $cols, $rows);

        $totalCells = $cols * $rows;
        $minArea = max(64, (int) ($totalCells * 0.001));

        // inset kecil agar foto tidak menempel/keluar tepi lubang (dalam piksel grid)
        $insetX = max(1, (int) floor($cols * 0.012));
        $insetY = max(1, (int) floor($rows * 0.012));

        $slots = [];
        foreach ($components as $comp) {
            if ($comp['area'] < $minArea) {
                continue;
            }
            $x = max(0, $comp['x'] + $insetX);
            $y = max(0, $comp['y'] + $insetY);
            $w = max(2, $comp['w'] - 2 * $insetX);
            $h = max(2, $comp['h'] - 2 * $insetY);
            $slots[] = [
                'x' => round($x / $cols, 4),
                'y' => round($y / $rows, 4),
                'w' => round($w / $cols, 4),
                'h' => round($h / $rows, 4),
            ];
        }

        // urut dari atas ke bawah, lalu kiri ke kanan
        usort($slots, fn ($a, $b) => $a['y'] <=> $b['y'] ?: $a['x'] <=> $b['x']);

        if (count($slots) === 0) {
            throw new RuntimeException('Tidak ada satu pun area foto transparan yang terdeteksi. Pastikan PNG memiliki lubang transparan untuk foto.');
        }

        if (count($slots) > 6) {
            throw new RuntimeException('Terlalu banyak area foto (maksimal 6).');
        }

        return $slots;
    }

    private function prepareGrid($src, int $srcW, int $srcH, int $maxDim = 1800)
    {
        // Resolusi penuh bila wajar agar slot presisi piksel; downscale hanya untuk PNG raksasa
        if (max($srcW, $srcH) <= $maxDim) {
            return $src;
        }

        return $this->resample($src, $srcW, $srcH, $maxDim);
    }

    private function resample($src, int $srcW, int $srcH, int $maxDim)
    {
        $scale = $maxDim / max($srcW, $srcH);
        $cols = max(2, (int) round($srcW * $scale));
        $rows = max(2, (int) round($srcH * $scale));

        $dst = imagecreatetruecolor($cols, $rows);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $cols, $rows, $srcW, $srcH);

        return $dst;
    }

    /**
     * Cari komponen tersambung dari sel transparan (BFS).
     */
    private function findComponents(array $grid, int $cols, int $rows): array
    {
        $visited = [];
        $components = [];

        $directions = [[1, 0], [-1, 0], [0, 1], [0, -1]];

        for ($y = 0; $y < $rows; $y++) {
            for ($x = 0; $x < $cols; $x++) {
                if (! $grid[$y][$x] || ! empty($visited[$y][$x])) {
                    continue;
                }

                $queue = [[$x, $y]];
                $visited[$y][$x] = true;
                $area = 0;
                $minX = $x;
                $maxX = $x;
                $minY = $y;
                $maxY = $y;

                while ($queue) {
                    [$cx, $cy] = array_pop($queue);
                    $area++;

                    $minX = min($minX, $cx);
                    $maxX = max($maxX, $cx);
                    $minY = min($minY, $cy);
                    $maxY = max($maxY, $cy);

                    foreach ($directions as [$dx, $dy]) {
                        $nx = $cx + $dx;
                        $ny = $cy + $dy;
                        if ($nx < 0 || $ny < 0 || $nx >= $cols || $ny >= $rows) {
                            continue;
                        }
                        if (! $grid[$ny][$nx] || ! empty($visited[$ny][$nx])) {
                            continue;
                        }
                        $visited[$ny][$nx] = true;
                        $queue[] = [$nx, $ny];
                    }
                }

                $components[] = [
                    'x' => $minX,
                    'y' => $minY,
                    'w' => $maxX - $minX + 1,
                    'h' => $maxY - $minY + 1,
                    'area' => $area,
                ];
            }
        }

        return $components;
    }
}

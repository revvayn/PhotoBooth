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

        // Downscale untuk analisis cepat (~200px paling besar)
        $grid = $this->downscale($src, $srcW, $srcH);
        imagedestroy($src);

        $cols = imagesx($grid);
        $rows = imagesy($grid);

        $alphaThreshold = 110; // alpha >= ini dianggap lubang transparan
        $step = max(1, (int) floor($rows / 120));

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
        $minArea = max(24, (int) ($totalCells * 0.004));

        $slots = [];
        foreach ($components as $comp) {
            if ($comp['area'] < $minArea) {
                continue;
            }
            $slots[] = [
                'x' => round($comp['x'] / $cols, 4),
                'y' => round($comp['y'] / $rows, 4),
                'w' => round($comp['w'] / $cols, 4),
                'h' => round($comp['h'] / $rows, 4),
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

    private function downscale($src, int $srcW, int $srcH, int $maxDim = 200)
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

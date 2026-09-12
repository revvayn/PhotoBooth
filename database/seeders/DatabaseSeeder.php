<?php

namespace Database\Seeders;

use App\Models\Frame;
use App\Models\User;
use App\Services\FrameAnalyzer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@photobooth.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@photobooth.com',
                'password' => Hash::make('password'),
            ]
        );

        Storage::disk('public')->deleteDirectory('frames');

        $analyzer = app(FrameAnalyzer::class);

        $definitions = [
            ['name' => 'K-pop Frame', 'slug' => 'kpop', 'category' => 'K-pop', 'caption' => 'K-POP', 'top' => [236, 72, 153], 'bottom' => [192, 132, 252], 'shots' => 3, 'sort_order' => 1],
            ['name' => 'Classic Frame', 'slug' => 'classic', 'category' => 'Classic', 'caption' => 'CLASSIC MOMENTS', 'top' => [212, 180, 131], 'bottom' => [120, 90, 40], 'shots' => 3, 'sort_order' => 2],
            ['name' => 'Minimalis Frame', 'slug' => 'minimalis', 'category' => 'Minimalis', 'caption' => 'MINIMALIS', 'top' => [226, 232, 240], 'bottom' => [148, 163, 184], 'shots' => 4, 'sort_order' => 3],
            ['name' => 'Neon Frame', 'slug' => 'neon', 'category' => 'Neon', 'caption' => 'NEON', 'top' => [217, 70, 239], 'bottom' => [34, 211, 238], 'shots' => 4, 'sort_order' => 4],
            ['name' => 'Estetik Frame', 'slug' => 'estetik', 'category' => 'Estetik', 'caption' => 'ESTETIK', 'top' => [167, 243, 208], 'bottom' => [56, 189, 248], 'shots' => 3, 'sort_order' => 5],
        ];

        foreach ($definitions as $def) {
            $path = $this->generatePng($def, $analyzer);

            Frame::updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'category' => $def['category'],
                    'caption' => $def['caption'],
                    'image_path' => 'frames/'.$def['slug'].'.png',
                    'photo_count' => $def['shots'],
                    'slots' => $analyzer->analyzePng(Storage::disk('public')->path($path)),
                    'is_active' => true,
                    'sort_order' => $def['sort_order'],
                ]
            );
        }
    }

    /**
     * Generate PNG frame sederhana dengan lubang transparan.
     */
    private function generatePng(array $def, FrameAnalyzer $analyzer): string
    {
        $W = 1080;
        $H = 1440;
        $margin = 96;          // bingkai luar
        $gap = 44;             // jarak antar lubang
        $shots = $def['shots'];

        $img = imagecreatetruecolor($W, $H);
        imagealphablending($img, true);

        // latar: gradien vertikal
        for ($y = 0; $y < $H; $y++) {
            $t = $y / $H;
            $r = (int) round($def['top'][0] + ($def['bottom'][0] - $def['top'][0]) * $t);
            $g = (int) round($def['top'][1] + ($def['bottom'][1] - $def['top'][1]) * $t);
            $b = (int) round($def['top'][2] + ($def['bottom'][2] - $def['top'][2]) * $t);
            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $W, $y, $color);
        }

        // panel dalam terang
        $panel = imagecolorallocatealpha($img, 255, 255, 255, 40);
        imagefilledrectangle($img, $margin, $margin, $W - $margin, $H - $margin, $panel);

        // lubang transparan
        $holeInner = $margin + 46;
        $holeW = 760;
        $usableH = $H - 2 * $holeInner - ($shots - 1) * $gap;
        $holeH = (int) floor($usableH / $shots);

        // watermark caption di tengah bawah
        $small = imagecolorallocatealpha($img, 255, 255, 255, 70);
        $font = 0;
        $spacing = 140;
        $startX = (int) (($W - (strlen($def['caption']) * $spacing)) / 2) + $spacing;
        for ($c = 0; $c < strlen($def['caption']); $c++) {
            imagestring($img, $font, $startX + $c * $spacing, (int) ($H * 0.06), $def['caption'][$c], $small);
        }

        // lubang transparan: blending dimatikan agar alpha ditulis presisi
        imagealphablending($img, false);

        for ($i = 0; $i < $shots; $i++) {
            $y = $holeInner + $i * ($holeH + $gap);
            $x = (int) (($W - $holeW) / 2);
            $this->drawHole($img, $x, $y, $holeW, $holeH, $def['shots'] === 4 ? 26 : 20);
        }

        // simpan PNG (alpha diawetkan)
        imagesavealpha($img, true);
        $path = 'frames/'.$def['slug'].'.png';
        Storage::disk('public')->makeDirectory(dirname($path));
        imagepng($img, Storage::disk('public')->path($path));
        imagedestroy($img);

        return $path;
    }

    private function drawHole($img, int $x, int $y, int $w, int $h, int $radius): void
    {
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        // ganti piksel area lubang menjadi transparan
        for ($py = $y; $py < $y + $h; $py++) {
            for ($px = $x; $px < $x + $w; $px++) {
                // radius sudut (sederhana): sisakan tepi melengkung
                if ($px < $x + $radius && $py < $y + $radius) {
                    $dx = $px - ($x + $radius);
                    $dy = $py - ($y + $radius);
                    if ($dx * $dx + $dy * $dy > $radius * $radius) {
                        continue;
                    }
                }
                if ($px >= $x + $w - $radius && $py < $y + $radius) {
                    $dx = $px - ($x + $w - $radius);
                    $dy = $py - ($y + $radius);
                    if ($dx * $dx + $dy * $dy > $radius * $radius) {
                        continue;
                    }
                }
                if ($px < $x + $radius && $py >= $y + $h - $radius) {
                    $dx = $px - ($x + $radius);
                    $dy = $py - ($y + $h - $radius);
                    if ($dx * $dx + $dy * $dy > $radius * $radius) {
                        continue;
                    }
                }
                if ($px >= $x + $w - $radius && $py >= $y + $h - $radius) {
                    $dx = $px - ($x + $w - $radius);
                    $dy = $py - ($y + $h - $radius);
                    if ($dx * $dx + $dy * $dy > $radius * $radius) {
                        continue;
                    }
                }
                imagesetpixel($img, $px, $py, $transparent);
            }
        }
    }
}

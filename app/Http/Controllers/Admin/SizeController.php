<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::withCount('frames')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('admin.sizes.create', [
            'size' => new Size([
                'width_cm' => 10.2,
                'height_cm' => 15.2,
                'price' => 30000,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSize($request);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $request->integer('sort_order', 0);

        Size::create($validated);

        return redirect()->route('admin.sizes.index')->with('success', 'Ukuran dan harga berhasil ditambahkan.');
    }

    public function edit(Size $size)
    {
        return view('admin.sizes.edit', compact('size'));
    }

    public function update(Request $request, Size $size)
    {
        $validated = $this->validateSize($request, $size);

        $dimensionsChanged = abs($size->width_cm - (float) $validated['width_cm']) > 0.001
            || abs($size->height_cm - (float) $validated['height_cm']) > 0.001;

        // Dimensi yang berubah akan membuat frame lama tidak lagi sesuai rasio.
        if ($dimensionsChanged && $size->frames()->exists()) {
            return back()
                ->withErrors(['width_cm' => 'Dimensi tidak bisa diubah karena sudah ada frame yang memakai ukuran ini. Buat ukuran baru untuk dimensi berbeda.'])
                ->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $oldName = $size->name;
        $size->update($validated);

        // Jaga kolom kategori lama tetap sinkron dengan nama ukuran.
        if ($oldName !== $size->name) {
            $size->frames()->update(['category' => $size->name]);
        }

        return redirect()->route('admin.sizes.index')->with('success', 'Ukuran dan harga berhasil diperbarui.');
    }

    public function destroy(Size $size)
    {
        if ($size->frames()->exists()) {
            return back()->with('error', 'Ukuran masih dipakai frame. Pindahkan frame ke ukuran lain sebelum menghapus.');
        }

        $size->delete();

        return redirect()->route('admin.sizes.index')->with('success', 'Ukuran berhasil dihapus.');
    }

    /**
     * Template PNG transparan sesuai rasio ukuran, siap dipakai admin sebagai
     * kanvas desain frame. Garis panduan berwarna dapat dihapus sebelum upload.
     */
    public function template(Size $size)
    {
        $pxPerCm = 300 / 2.54;
        $width = max(320, (int) round($size->width_cm * $pxPerCm));
        $height = max(320, (int) round($size->height_cm * $pxPerCm));

        $longest = max($width, $height);
        if ($longest > 3200) {
            $ratio = 3200 / $longest;
            $width = max(320, (int) round($width * $ratio));
            $height = max(320, (int) round($height * $ratio));
        }

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagealphablending($image, true);

        $guide = imagecolorallocatealpha($image, 225, 29, 72, 0);
        $soft = imagecolorallocatealpha($image, 148, 163, 184, 20);

        $this->dashedRect($image, 1, 1, $width - 2, $height - 2, $guide);
        $this->dashedRect(
            $image,
            (int) round($width * 0.04),
            (int) round($height * 0.04),
            (int) round($width * 0.92),
            (int) round($height * 0.92),
            $soft
        );

        $label = $size->name.'  '.rtrim(rtrim(number_format($size->width_cm, 2, '.', ''), '0'), '.')
            .' x '.rtrim(rtrim(number_format($size->height_cm, 2, '.', ''), '0'), '.').' cm';
        $legend = 'Template '.$width.' x '.$height.' px - hapus garis panduan sebelum upload';

        imagestring($image, 5, 26, 26, $label, $guide);
        imagestring($image, 3, 26, 48, $legend, $guide);
        imagestring($image, 3, 26, $height - 40, 'Area desain frame', $soft);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$size->templateFileName().'"',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSize(Request $request, ?Size $size = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sizes', 'name')->ignore($size?->id),
            ],
            'width_cm' => ['required', 'numeric', 'min:1', 'max:200'],
            'height_cm' => ['required', 'numeric', 'min:1', 'max:200'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'name.unique' => 'Nama ukuran sudah dipakai.',
        ]);
    }

    private function dashedRect($image, int $x1, int $y1, int $x2, int $y2, int $color): void
    {
        $dash = max(10, (int) round(max($x2 - $x1, $y2 - $y1) / 60));
        $gap = (int) round($dash * 0.7);

        $this->dashedLine($image, $x1, $y1, $x2, $y1, $color, $dash, $gap);
        $this->dashedLine($image, $x2, $y1, $x2, $y2, $color, $dash, $gap);
        $this->dashedLine($image, $x2, $y2, $x1, $y2, $color, $dash, $gap);
        $this->dashedLine($image, $x1, $y2, $x1, $y1, $color, $dash, $gap);
    }

    private function dashedLine($image, int $x1, int $y1, int $x2, int $y2, int $color, int $dash, int $gap): void
    {
        $length = (int) round(hypot($x2 - $x1, $y2 - $y1));
        if ($length <= 0) {
            return;
        }

        $step = $dash + $gap;
        for ($offset = 0; $offset < $length; $offset += $step) {
            $start = $offset / $length;
            $end = min($length, $offset + $dash) / $length;
            imageline(
                $image,
                (int) round($x1 + ($x2 - $x1) * $start),
                (int) round($y1 + ($y2 - $y1) * $start),
                (int) round($x1 + ($x2 - $x1) * $end),
                (int) round($y1 + ($y2 - $y1) * $end),
                $color
            );
        }
    }
}

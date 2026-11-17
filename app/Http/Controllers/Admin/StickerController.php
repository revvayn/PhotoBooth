<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sticker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StickerController extends Controller
{
    public function index()
    {
        $stickers = Sticker::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.stickers.index', compact('stickers'));
    }

    public function create()
    {
        return view('admin.stickers.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'emoji' => $validated['type'] === 'emoji' ? trim((string) $validated['emoji']) : null,
            'is_active' => $request->boolean('is_active', false),
            'sort_order' => $request->integer('sort_order', 0),
        ];

        if ($validated['type'] === 'image') {
            $data['image_path'] = $this->storeImage($request->file('sticker_image'));
        }

        Sticker::create($data);

        return redirect()->route('admin.stickers.index')->with('success', 'Stiker berhasil ditambahkan.');
    }

    public function edit(Sticker $sticker)
    {
        return view('admin.stickers.edit', compact('sticker'));
    }

    public function update(Request $request, Sticker $sticker)
    {
        $validated = $this->validated($request, $sticker);

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'emoji' => $validated['type'] === 'emoji' ? trim((string) $validated['emoji']) : null,
            'is_active' => $request->boolean('is_active', false),
            'sort_order' => $request->integer('sort_order', 0),
        ];

        if ($validated['type'] === 'image') {
            if ($request->hasFile('sticker_image')) {
                $this->deleteImage($sticker);
                $data['image_path'] = $this->storeImage($request->file('sticker_image'));
            }
        } else {
            // Beralih ke emoji: gambar lama tidak dipakai lagi.
            $this->deleteImage($sticker);
            $data['image_path'] = null;
        }

        $sticker->update($data);

        return redirect()->route('admin.stickers.index')->with('success', 'Stiker berhasil diperbarui.');
    }

    public function destroy(Sticker $sticker)
    {
        $this->deleteImage($sticker);
        $sticker->delete();

        return redirect()->route('admin.stickers.index')->with('success', 'Stiker berhasil dihapus.');
    }

    private function validated(Request $request, ?Sticker $sticker = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['emoji', 'image'])],
            'emoji' => ['nullable', 'string', 'max:32'],
            'sticker_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validated['type'] === 'emoji') {
            $emoji = trim((string) ($validated['emoji'] ?? ''));
            if ($emoji === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'emoji' => 'Isi karakter emoji untuk stiker ini.',
                ]);
            }
        }

        // Saat membuat / mengganti gambar, unggahan wajib ada. Saat edit tipe
        // gambar tanpa unggahan baru, gambar lama dipertahankan.
        $hasImage = $sticker
            && $sticker->type === 'image'
            && $sticker->image_path
            && ! $request->hasFile('sticker_image');

        if ($validated['type'] === 'image' && ! $request->hasFile('sticker_image') && ! $hasImage) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sticker_image' => 'Unggah gambar stiker (PNG, JPG, atau WEBP).',
            ]);
        }

        return $validated;
    }

    private function storeImage($file): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $name = 'stickers/'.bin2hex(random_bytes(10)).'.'.strtolower($extension);
        $file->storeAs('stickers', basename($name), 'public');

        return $name;
    }

    private function deleteImage(Sticker $sticker): void
    {
        if ($sticker->image_path) {
            Storage::disk('public')->delete($sticker->image_path);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\Size;
use App\Services\FrameAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FrameController extends Controller
{
    public function __construct(private FrameAnalyzer $analyzer) {}

    public function index()
    {
        $frames = Frame::with('size')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.frames.index', compact('frames'));
    }

    public function create()
    {
        return view('admin.frames.create', [
            'sizes' => $this->sizesForForm(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'size_id' => 'required|integer|exists:sizes,id',
            'caption' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'price' => 'nullable|integer|min:0',
            'frame_image' => 'required|image|mimes:png',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (Frame::where('slug', $validated['slug'])->exists()) {
            return back()->withErrors(['name' => 'Nama frame sudah digunakan, gunakan nama lain.'])->withInput();
        }

        $size = Size::findOrFail($validated['size_id']);
        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['sort_order'] = $request->integer('sort_order', 0);
        $validated['price'] = $request->filled('price') ? (int) $validated['price'] : null;
        // Kategori lama tetap diisi agar tampilan/laporan lama tidak kosong.
        $validated['category'] = $size->name;
        unset($validated['frame_image'], $validated['size_id']);

        $frame = Frame::create($validated + ['size_id' => $size->id]);

        try {
            $this->saveFrameImage($frame, $request->file('frame_image'), $size);
        } catch (RuntimeException $e) {
            $frame->delete();

            return back()->withErrors(['frame_image' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.frames.index')->with('success', 'Frame berhasil ditambahkan.');
    }

    public function edit(Frame $frame)
    {
        return view('admin.frames.edit', [
            'frame' => $frame,
            'sizes' => $this->sizesForForm(),
        ]);
    }

    public function update(Request $request, Frame $frame)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'size_id' => 'required|integer|exists:sizes,id',
            'caption' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'price' => 'nullable|integer|min:0',
            'frame_image' => 'nullable|image|mimes:png',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (Frame::where('slug', $validated['slug'])->where('id', '!=', $frame->id)->exists()) {
            return back()->withErrors(['name' => 'Nama frame sudah digunakan, gunakan nama lain.'])->withInput();
        }

        $size = Size::findOrFail($validated['size_id']);
        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['sort_order'] = $request->integer('sort_order', 0);
        $validated['price'] = $request->filled('price') ? (int) $validated['price'] : null;
        $validated['category'] = $size->name;
        $validated['size_id'] = $size->id;
        unset($validated['frame_image']);

        // Ukuran diubah tanpa upload baru: gambar lama wajib sudah sesuai.
        if (! $request->hasFile('frame_image') && $frame->image_path) {
            $existing = Storage::disk('public')->path($frame->image_path);
            try {
                $this->assertImageMatchesSize($existing, $size);
            } catch (RuntimeException $e) {
                return back()->withErrors(['size_id' => $e->getMessage()])->withInput();
            }
        }

        $frame->update($validated);

        if ($request->hasFile('frame_image')) {
            try {
                $this->saveFrameImage($frame, $request->file('frame_image'), $size);
            } catch (RuntimeException $e) {
                return back()->withErrors(['frame_image' => $e->getMessage()])->withInput();
            }
        }

        return redirect()->route('admin.frames.index')->with('success', 'Frame berhasil diperbarui.');
    }

    public function destroy(Frame $frame)
    {
        $this->deleteFrameImage($frame);
        $frame->delete();

        return redirect()->route('admin.frames.index')->with('success', 'Frame berhasil dihapus.');
    }

    private function saveFrameImage(Frame $frame, $upload, Size $size): void
    {
        $path = $upload->getRealPath();
        $this->assertImageMatchesSize($path, $size);

        $targetPath = 'frames/'.$frame->slug.'.png';
        $slots = $this->analyzer->analyzePng($path);

        $this->deleteFrameImage($frame);

        $upload->storeAs('frames', $frame->slug.'.png', 'public');

        $frame->update([
            'image_path' => $targetPath,
            'photo_count' => count($slots),
            'slots' => $slots,
        ]);
    }

    /**
     * Tolak frame yang rasionya tidak sama dengan ukuran kertas terpilih.
     */
    private function assertImageMatchesSize(string $path, Size $size): void
    {
        if (! is_file($path)) {
            throw new RuntimeException('Berkas gambar frame tidak ditemukan.');
        }

        $info = @getimagesize($path);
        if (! $info) {
            throw new RuntimeException('Berkas harus berupa PNG yang valid.');
        }

        $width = (int) $info[0];
        $height = (int) $info[1];

        if ($size->matchesDimensions($width, $height)) {
            return;
        }

        throw new RuntimeException(
            'Rasio gambar frame ('.$width.'×'.$height.' px) tidak sesuai ukuran '.$size->name
            .' ('.$size->dimension_label.'). Unduh template ukuran tersebut, desain frame di kanvas template, lalu unggah ulang.'
        );
    }

    private function sizesForForm()
    {
        return Size::orderBy('sort_order')->orderBy('name')->get();
    }

    private function deleteFrameImage(Frame $frame): void
    {
        if ($frame->image_path) {
            Storage::disk('public')->delete($frame->image_path);
        }
    }
}

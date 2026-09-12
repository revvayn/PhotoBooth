<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Frame;
use App\Services\FrameAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FrameController extends Controller
{
    public function __construct(private FrameAnalyzer $analyzer) {}

    public function index()
    {
        $frames = Frame::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.frames.index', compact('frames'));
    }

    public function create()
    {
        return view('admin.frames.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'frame_image' => 'required|image|mimes:png',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (Frame::where('slug', $validated['slug'])->exists()) {
            return back()->withErrors(['name' => 'Nama frame sudah digunakan, gunakan nama lain.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $frame = Frame::create($validated);

        try {
            $this->saveFrameImage($frame, $request->file('frame_image'));
        } catch (RuntimeException $e) {
            $frame->delete();

            return back()->withErrors(['frame_image' => $e->getMessage()])->withInput();
        }

        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => 'admin',
            'action' => 'frame.created',
            'metadata' => ['frame_name' => $frame->name, 'admin' => Auth::guard('auth')->user()?->email ?? 'admin'],
        ]);

        return redirect()->route('admin.frames.index')->with('success', 'Frame berhasil ditambahkan.');
    }

    public function edit(Frame $frame)
    {
        return view('admin.frames.edit', compact('frame'));
    }

    public function update(Request $request, Frame $frame)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'frame_image' => 'nullable|image|mimes:png',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (Frame::where('slug', $validated['slug'])->where('id', '!=', $frame->id)->exists()) {
            return back()->withErrors(['name' => 'Nama frame sudah digunakan, gunakan nama lain.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $frame->update($validated);

        if ($request->hasFile('frame_image')) {
            try {
                $this->saveFrameImage($frame, $request->file('frame_image'));
            } catch (RuntimeException $e) {
                return back()->withErrors(['frame_image' => $e->getMessage()])->withInput();
            }
        }

        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => 'admin',
            'action' => 'frame.updated',
            'metadata' => ['frame_name' => $frame->name, 'admin' => Auth::guard('auth')->user()?->email ?? 'admin'],
        ]);

        return redirect()->route('admin.frames.index')->with('success', 'Frame berhasil diperbarui.');
    }

    public function destroy(Frame $frame)
    {
        $frameName = $frame->name;

        $this->deleteFrameImage($frame);
        $frame->delete();

        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => 'admin',
            'action' => 'frame.deleted',
            'metadata' => ['frame_name' => $frameName, 'admin' => Auth::guard('auth')->user()?->email ?? 'admin'],
        ]);

        return redirect()->route('admin.frames.index')->with('success', 'Frame berhasil dihapus.');
    }

    private function saveFrameImage(Frame $frame, $upload): void
    {
        $targetPath = 'frames/'.$frame->slug.'.png';

        $slots = $this->analyzer->analyzePng($upload->getRealPath());

        $this->deleteFrameImage($frame);

        $upload->storeAs('frames', $frame->slug.'.png', 'public');

        $frame->update([
            'image_path' => $targetPath,
            'photo_count' => count($slots),
            'slots' => $slots,
        ]);
    }

    private function deleteFrameImage(Frame $frame): void
    {
        if ($frame->image_path) {
            Storage::disk('public')->delete($frame->image_path);
        }
    }
}

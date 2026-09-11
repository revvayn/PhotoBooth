<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FrameController extends Controller
{
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
            'color_class' => 'nullable|string|max:255',
            'border_class' => 'nullable|string|max:255',
            'accent_class' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $frame = Frame::create($validated);

        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => 'admin',
            'action' => 'frame.created',
            'metadata' => ['frame_name' => $frame->name, 'admin' => auth()->user()->email],
        ]);

        return redirect()->route('admin.frames.index')->with('success', 'Frame created successfully.');
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
            'color_class' => 'nullable|string|max:255',
            'border_class' => 'nullable|string|max:255',
            'accent_class' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', false);
        $validated['sort_order'] = $request->integer('sort_order', 0);

        $frame->update($validated);

        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => 'admin',
            'action' => 'frame.updated',
            'metadata' => ['frame_name' => $frame->name, 'admin' => auth()->user()->email],
        ]);

        return redirect()->route('admin.frames.index')->with('success', 'Frame updated successfully.');
    }

    public function destroy(Frame $frame)
    {
        $frameName = $frame->name;

        $frame->delete();

        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => 'admin',
            'action' => 'frame.deleted',
            'metadata' => ['frame_name' => $frameName, 'admin' => auth()->user()->email],
        ]);

        return redirect()->route('admin.frames.index')->with('success', 'Frame deleted successfully.');
    }
}

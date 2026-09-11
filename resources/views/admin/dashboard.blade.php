@extends('layouts.admin')

@section('title', 'Dashboard - Photobooth Admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        {{-- Total Sesi Hari Ini --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Sesi Hari Ini</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalSessionsToday }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-blue-50 rounded-xl">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Foto --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Foto</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalPhotos }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-rose-50 rounded-xl">
                    <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Frame --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Frame</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalFrames }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-amber-50 rounded-xl">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Log --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Log</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalLogs }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-emerald-50 rounded-xl">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity Logs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-heading text-lg font-bold text-slate-800">Recent Activity</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Waktu</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Session ID</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Aksi</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Detail</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentLogs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ \Illuminate\Support\Str::limit($log->session_id, 12) }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $actionColors = [
                                        'session.started'      => 'bg-blue-100 text-blue-700',
                                        'foto.taken'           => 'bg-rose-100 text-rose-700',
                                        'filter.selected'      => 'bg-fuchsia-100 text-fuchsia-700',
                                        'frame.selected'       => 'bg-amber-100 text-amber-700',
                                        'print.selected'       => 'bg-violet-100 text-violet-700',
                                        'metode.selected'      => 'bg-cyan-100 text-cyan-700',
                                        'pembayaran.confirmed' => 'bg-emerald-100 text-emerald-700',
                                        'email.sent'           => 'bg-teal-100 text-teal-700',
                                        'session.completed'    => 'bg-slate-100 text-slate-700',
                                        'frame.created'        => 'bg-green-100 text-green-700',
                                        'frame.updated'        => 'bg-blue-100 text-blue-700',
                                        'frame.deleted'        => 'bg-red-100 text-red-700',
                                    ];
                                    $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs max-w-xs truncate">{{ is_array($log->metadata) ? json_encode($log->metadata) : $log->metadata }}</td>
                            <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ is_array($log->metadata) ? ($log->metadata['ip'] ?? '-') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Belum ada activity log
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentLogs->count() > 0)
            <div class="px-6 py-3 border-t border-gray-200 text-right">
                <a href="{{ route('admin.logs.index') }}" class="text-sm text-rose-500 hover:text-rose-600 font-medium transition-colors">
                    Lihat semua log &rarr;
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

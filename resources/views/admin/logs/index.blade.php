@extends('layouts.admin')

@section('title', 'Activity Log - Photobooth Admin')
@section('page-title', 'Activity Log')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-slate-500 text-sm">Riwayat aktivitas seluruh sesi photobooth</p>
        <span class="text-xs text-slate-400">{{ $logs->total() }} total entri</span>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Waktu</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Session ID</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Queue</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">Aksi</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600 max-w-xs">Detail</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-600">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">
                                <div class="text-sm">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-slate-400">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-slate-500">
                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 rounded-md" title="{{ $log->session_id }}">
                                    {{ \Illuminate\Support\Str::limit($log->session_id, 16, '') }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $log->queue_number ?? '-' }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $actionStyles = [
                                        'session.started'      => 'bg-blue-100 text-blue-700 ring-blue-600/20',
                                        'foto.taken'           => 'bg-rose-100 text-rose-700 ring-rose-600/20',
                                        'filter.selected'      => 'bg-fuchsia-100 text-fuchsia-700 ring-fuchsia-600/20',
                                        'frame.selected'       => 'bg-amber-100 text-amber-700 ring-amber-600/20',
                                        'print.selected'       => 'bg-violet-100 text-violet-700 ring-violet-600/20',
                                        'metode.selected'      => 'bg-cyan-100 text-cyan-700 ring-cyan-600/20',
                                        'pembayaran.confirmed' => 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
                                        'email.sent'           => 'bg-teal-100 text-teal-700 ring-teal-600/20',
                                        'session.completed'    => 'bg-slate-100 text-slate-600 ring-slate-500/20',
                                        'frame.created'        => 'bg-green-100 text-green-700 ring-green-600/20',
                                        'frame.updated'        => 'bg-blue-100 text-blue-700 ring-blue-600/20',
                                        'frame.deleted'        => 'bg-red-100 text-red-700 ring-red-600/20',
                                    ];
                                    $style = $actionStyles[$log->action] ?? 'bg-gray-100 text-gray-600 ring-gray-500/20';
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $style }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-3 max-w-xs">
                                @if($log->metadata && (is_array($log->metadata) ? count($log->metadata) > 0 : true))
                                    <div class="group relative">
                                        <span class="text-xs text-slate-500 truncate block max-w-[200px] cursor-default">
                                            {{ is_array($log->metadata) ? json_encode($log->metadata) : $log->metadata }}
                                        </span>
                                        @if(is_array($log->metadata) && count($log->metadata) > 0)
                                            <div class="hidden group-hover:block absolute z-10 bottom-full left-0 mb-2 w-72 p-3 bg-slate-800 text-white text-xs rounded-lg shadow-xl font-mono whitespace-pre-wrap break-all">
                                                @json($log->metadata, JSON_PRETTY_PRINT)
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ is_array($log->metadata) ? ($log->metadata['ip'] ?? '-') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                Belum ada activity log
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($logs, 'links') && $logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

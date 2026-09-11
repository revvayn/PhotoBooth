<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoboothController extends Controller
{
    private const PRICES = [1 => 30000, 2 => 35000, 3 => 40000, 4 => 45000];

    private const FILTERS = [
        'asli' => 'Asli',
        'bw' => 'Hitam Putih',
        'vintage' => 'Vintage',
        'warm' => 'Warm',
        'cool' => 'Cool',
        'fade' => 'Fade',
        'contrast' => 'Contrast',
        'neon' => 'Neon',
    ];

    private const SESSION_SECONDS = 300;

    public function welcome()
    {
        return view('welcome');
    }

    public function mulai(Request $request)
    {
        $request->session()->regenerate();
        $queue = '#'.rand(1000, 9999);

        $request->session()->put('queue', $queue);
        $request->session()->put('started_at', now()->timestamp);
        $request->session()->put('total_seconds', self::SESSION_SECONDS);
        $request->session()->forget(['photos', 'filter', 'frame', 'copy', 'metode', 'paid', 'email']);

        $this->log('session.started', ['queue' => $queue]);

        return redirect()->route('tutorial');
    }

    public function tutorial(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        return view('tutorial', [
            'queue' => session('queue'),
            'step' => 1,
        ]);
    }

    public function frame(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        $allFrames = Frame::active()->orderBy('sort_order')->get()->keyBy('slug');
        $categories = Frame::active()->orderBy('sort_order')->get()->pluck('category')->unique()->values()->toArray();

        return view('frame', [
            'categories' => $categories,
            'allFrames' => $allFrames,
            'step' => 2,
        ]);
    }

    public function frameStore(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        $activeSlugs = Frame::active()->pluck('slug')->toArray();

        $data = $request->validate([
            'frame' => ['required', 'in:'.implode(',', $activeSlugs)],
        ]);

        $request->session()->put('frame', $data['frame']);

        $frame = Frame::where('slug', $data['frame'])->first();
        $this->log('frame.selected', ['frame' => $frame?->name ?? $data['frame']]);

        return redirect()->route('foto');
    }

    public function foto(Request $request)
    {
        $this->requirePrereq($request, ['frame']);

        return view('foto', [
            'queue' => session('queue'),
            'step' => 2,
        ]);
    }

    public function fotoStore(Request $request)
    {
        $this->requirePrereq($request, ['frame']);

        $data = $request->validate([
            'photos' => ['required', 'array', 'size:3'],
            'photos.*' => ['required', 'string'],
        ]);

        $path = 'photos/'.$request->session()->getId();
        Storage::disk('public')->deleteDirectory($path);

        $saved = [];
        foreach (['1', '2', '3'] as $i => $key) {
            $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $data['photos'][$i]);
            if (! $base64 || base64_decode($base64, true) === false) {
                return response()->json(['message' => 'Foto tidak valid.'], 422);
            }
            $filename = 'foto-'.$key.'.jpg';
            Storage::disk('public')->put($path.'/'.$filename, base64_decode($base64));
            $saved[] = Storage::disk('public')->url($path.'/'.$filename);
        }

        $request->session()->put('photos', $saved);

        $this->log('foto.taken', ['count' => 3]);

        return response()->json(['redirect' => route('filter')]);
    }

    public function filter(Request $request)
    {
        $this->requirePrereq($request, ['photos']);

        return view('filter', [
            'filters' => self::FILTERS,
            'photos' => session('photos', []),
            'step' => 2,
        ]);
    }

    public function filterStore(Request $request)
    {
        $this->requirePrereq($request, ['photos']);

        $data = $request->validate([
            'filter' => ['required', 'in:'.implode(',', array_keys(self::FILTERS))],
        ]);

        $request->session()->put('filter', $data['filter']);

        $this->log('filter.selected', ['filter' => $data['filter']]);

        return redirect()->route('print');
    }

    public function printCount(Request $request)
    {
        $this->requirePrereq($request, ['filter']);

        return view('print', [
            'prices' => self::PRICES,
            'basePrice' => self::PRICES[1],
            'step' => 3,
        ]);
    }

    public function printStore(Request $request)
    {
        $this->requirePrereq($request, ['filter']);

        $data = $request->validate([
            'copy' => ['required', 'in:'.implode(',', array_keys(self::PRICES))],
        ]);

        $request->session()->put('copy', (int) $data['copy']);

        $this->log('print.selected', ['copy' => $data['copy'], 'total' => $this->total()]);

        return redirect()->route('metode');
    }

    public function metode(Request $request)
    {
        $this->requirePrereq($request, ['copy']);

        return view('metode', [
            'total' => $this->total(),
            'step' => 3,
        ]);
    }

    public function metodeStore(Request $request)
    {
        $this->requirePrereq($request, ['copy']);

        $data = $request->validate([
            'metode' => ['required', 'in:cash,qris'],
        ]);

        $request->session()->put('metode', $data['metode']);

        $this->log('metode.selected', ['metode' => $data['metode']]);

        return redirect()->route('pembayaran');
    }

    public function pembayaran(Request $request)
    {
        $this->requirePrereq($request, ['metode']);

        return view('pembayaran', [
            'metode' => session('metode'),
            'total' => $this->total(),
            'nmid' => '102020034073193',
            'merchant' => 'PERKAKASKU',
            'queue' => session('queue'),
            'step' => 3,
        ]);
    }

    public function pembayaranStore(Request $request)
    {
        $this->requirePrereq($request, ['metode']);

        $request->session()->put('paid', true);

        $this->log('pembayaran.confirmed', ['total' => $this->total(), 'metode' => session('metode')]);

        return redirect()->route('review');
    }

    public function review(Request $request)
    {
        $this->requirePrereq($request, ['paid']);

        return view('review', [
            'photos' => session('photos', []),
            'filter' => session('filter', 'asli'),
            'frame' => session('frame', 'kpop'),
            'frameName' => $this->getFrameName(session('frame', 'kpop')),
            'total' => $this->total(),
            'queue' => session('queue'),
            'step' => 3,
        ]);
    }

    public function emailStore(Request $request)
    {
        $this->requirePrereq($request, ['paid']);

        $data = $request->validate([
            'email' => ['required', 'email:rfc'],
        ]);

        $request->session()->put('email', $data['email']);

        $this->log('email.sent', ['email' => $data['email']]);

        return response()->json(['redirect' => route('selesai')]);
    }

    public function selesai(Request $request)
    {
        $this->requirePrereq($request, ['paid']);

        $this->log('session.completed');

        return view('selesai', [
            'photos' => session('photos', []),
            'filter' => session('filter', 'asli'),
            'frame' => session('frame', 'kpop'),
            'email' => session('email'),
            'queue' => session('queue'),
            'step' => 3,
        ]);
    }

    public function selesaiStore(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function download(Request $request)
    {
        $this->requirePrereq($request, ['photos']);

        $first = session('photos')[0];
        $name = 'fotobooth-'.time().'.jpg';

        return response()->streamDownload(function () use ($first) {
            echo file_get_contents(base_path('public'.parse_url($first, PHP_URL_PATH)));
        }, $name, ['Content-Type' => 'image/jpeg']);
    }

    private function total(): int
    {
        $copy = (int) session('copy', 1);

        return self::PRICES[$copy] ?? self::PRICES[1];
    }

    private function getFrameName(string $slug): string
    {
        $frame = Frame::where('slug', $slug)->first();

        return $frame?->name ?? ucfirst($slug).' Frame';
    }

    /**
     * Pastikan alur dilalui secara berurutan.
     * Setiap halaman hanya bisa diakses jika data prasyaratnya sudah ada.
     */
    private function requirePrereq(Request $request, array $keys): void
    {
        if (! $request->session()->has('queue')) {
            abort(redirect()->route('home'));
        }

        $missing = array_filter($keys, fn (string $key) => ! $request->session()->has($key));

        if ($missing) {
            abort(redirect()->route('home'));
        }
    }

    private function log(string $action, array $metadata = []): void
    {
        ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => session('queue'),
            'action' => $action,
            'metadata' => ['ip' => request()->ip()] + $metadata,
        ]);
    }
}

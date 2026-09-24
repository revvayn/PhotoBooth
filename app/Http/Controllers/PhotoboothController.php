<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoboothController extends Controller
{
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

    /**
     * Kanvas SPA — 1 dokumen penuh berisi SEMUA panel sekaligus.
     * Alur boot: '/' (Mulai) -> POST /mulai -> /tutorial -> /spa.
     * Panel yang tampil ditentukan server via ?step= / session spa_step
     * (default 'frame') agar tiap tahap selalu ter-render penuh dengan data sesi.
     */
    public function spa(Request $request)
    {
        if (! $request->session()->has('queue')) {
            $queue = '#'.rand(1000, 9999 );
            $request->session()->put('queue', $queue);
            $request->session()->put('started_at', now()->timestamp);
            $request->session()->put('total_seconds', self::SESSION_SECONDS);
            $this->resetCartSession($request);
        }

        $validSteps = ['frame', 'foto', 'filter', 'metode', 'pembayaran', 'review'];
        $step = $request->query('step', $request->session()->get('spa_step', 'frame'));
        if (! in_array($step, $validSteps, true)) {
            $step = 'frame';
        }

        // Samakan preseden requirePrereq: tahap hanya bisa dibuka jika data
        // prasyaratnya ada di sesi; permintaan melompat dijepit ke tahap
        // terjauh yang datanya lengkap (mis. sesi baru selalu jatuh ke frame).
        $needs = [
            'frame' => [],
            'foto' => ['cart'],
            'filter' => ['photos'],
            'metode' => ['filter'],
            'pembayaran' => ['metode'],
            'review' => ['paid'],
        ];
        $max = 0;
        foreach ($validSteps as $i => $key) {
            foreach ($needs[$key] as $k) {
                if (! $request->session()->has($k)) {
                    break 2;
                }
            }
            $max = $i;
        }
        $idx = array_search($step, $validSteps, true);
        if ($idx === false || $idx > $max) {
            $step = $validSteps[$max];
        }
        $request->session()->put('spa_step', $step);

        $allFrames = Frame::active()->orderBy('sort_order')->get()->keyBy('slug');
        $categories = Frame::active()->orderBy('sort_order')->get()
            ->pluck('category')->unique()->filter()->values()->toArray();

        $photos = collect(session('photos', []))->flatten()->values()->all();

        $items = collect($this->cartItems())->map(function ($item) use ($photos) {
            $item['photos'] = session('photos', [])[$item['slug']] ?? [];

            return $item;
        })->values()->all();

        return view('layouts.spa', [
            'queue' => session('queue'),
            'step' => $step,
            'categories' => $categories,
            'allFrames' => $allFrames,
            'cart' => session('cart', []),
            'items' => $items,
            'photoCount' => $this->maxPhotoCount(),
            'photos' => $photos,
            'filters' => self::FILTERS,
            'filter' => session('filter', 'asli'),
            'total' => $this->total(),
            'email' => session('email'),
            'metode' => session('metode'),
            'nmid' => '102020034073193',
            'merchant' => 'PERKAKASKU',
        ]);
    }

    public function mulai(Request $request)
    {
        $request->session()->regenerate();
        $queue = '#'.rand(1000, 9999);

        $request->session()->put('queue', $queue);
        $request->session()->put('started_at', now()->timestamp);
        $request->session()->put('total_seconds', self::SESSION_SECONDS);

        $this->resetCartSession($request);

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
        $categories = Frame::active()->orderBy('sort_order')->get()->pluck('category')->unique()->filter()->values()->toArray();

        return view('frame', [
            'categories' => $categories,
            'allFrames' => $allFrames,
            'cart' => session('cart', []),
            'step' => 2,
        ]);
    }

    public function frameStore(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        $activeSlugs = Frame::active()->pluck('slug')->toArray();

        $data = $request->validate([
            'cart' => ['required', 'array', 'min:1', 'max:'.count($activeSlugs)],
            'cart.*' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $cart = [];
        foreach ($data['cart'] as $slug => $qty) {
            if (in_array($slug, $activeSlugs, true)) {
                $cart[$slug] = (int) $qty;
            }
        }

        if (! $cart) {
            return back()->withErrors(['cart' => 'Pilih minimal satu frame.']);
        }

        $request->session()->put('cart', $cart);
        // Frame/key foto berubah => hasil foto lama tidak berlaku lagi.
        $this->resetCartSession($request, ['cart']);

        $frames = Frame::whereIn('slug', array_keys($cart))->get();
        $this->log('frame.selected', [
            'frames' => $frames->map(fn ($f) => $f->name.' ├ù'.$cart[$f->slug])->implode(' + '),
            'total' => $this->total(),
        ]);

        if ($request->boolean('from_spa')) {
            $request->session()->put('spa_step', 'foto');

            return redirect()->route('spa', ['step' => 'foto']);
        }

        return redirect()->route('foto');
    }

    public function foto(Request $request)
    {
        $this->requirePrereq($request, ['cart']);

        return view('foto', [
            'queue' => session('queue'),
            'items' => $this->cartItems(),
            'photoCount' => $this->maxPhotoCount(),
            'step' => 2,
        ]);
    }

    public function fotoStore(Request $request)
    {
        $this->requirePrereq($request, ['cart']);

        $photoCount = $this->maxPhotoCount();

        $data = $request->validate([
            'photos' => ['required', 'array', 'size:'.$photoCount],
            'photos.*' => ['required', 'string'],
        ]);

        $path = 'photos/'.$request->session()->getId();
        Storage::disk('public')->deleteDirectory($path);

        $saved = [];
        $index = 0;
        foreach ($this->cartItems() as $item) {
            $savedItem = [];
            foreach (range(1, $item['photo_count']) as $key) {
                $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $data['photos'][$index]);
                if (! $base64 || base64_decode($base64, true) === false) {
                    return response()->json(['message' => 'Foto tidak valid.'], 422);
                }
                $filename = 'foto-'.($index + 1).'.jpg';
                Storage::disk('public')->put($path.'/'.$filename, base64_decode($base64));
                // gunakan URL relatif-akar agar bekerja di mana pun kiosk diakses
                $savedItem[] = '/storage/'.$path.'/'.$filename;
                $index++;
            }
            $saved[$item['slug']] = $savedItem;
        }

        $request->session()->put('photos', $saved);

        $this->log('foto.taken', ['count' => $photoCount]);

        return response()->json(['redirect' => route('filter')]);
    }

    public function filter(Request $request)
    {
        $this->requirePrereq($request, ['photos']);

        return view('filter', [
            'filters' => self::FILTERS,
            'photos' => collect(session('photos', []))->flatten()->values()->all(),
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

        if ($request->boolean('from_spa')) {
            $request->session()->put('spa_step', 'metode');

            return redirect()->route('spa', ['step' => 'metode']);
        }

        return redirect()->route('metode');
    }

    public function metode(Request $request)
    {
        $this->requirePrereq($request, ['filter']);

        return view('metode', [
            'total' => $this->total(),
            'step' => 3,
        ]);
    }

    public function metodeStore(Request $request)
    {
        $this->requirePrereq($request, ['filter']);

        $data = $request->validate([
            'metode' => ['required', 'in:cash,qris'],
        ]);

        $request->session()->put('metode', $data['metode']);

        $this->log('metode.selected', ['metode' => $data['metode']]);

        if ($request->boolean('from_spa')) {
            $request->session()->put('spa_step', 'pembayaran');

            return redirect()->route('spa', ['step' => 'pembayaran']);
        }

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

        if ($request->boolean('from_spa')) {
            $request->session()->put('spa_step', 'review');

            return redirect()->route('spa', ['step' => 'review']);
        }

        return redirect()->route('review');
    }

    public function review(Request $request)
    {
        $this->requirePrereq($request, ['paid']);

        $photos = session('photos', []);

        $items = collect($this->cartItems())->map(function ($item) use ($photos) {
            $item['photos'] = $photos[$item['slug']] ?? [];

            return $item;
        });

        return view('review', [
            'items' => $items,
            'filter' => session('filter', 'asli'),
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

        $photos = session('photos', []);

        $items = collect($this->cartItems())->map(function ($item) use ($photos) {
            $item['photos'] = $photos[$item['slug']] ?? [];

            return $item;
        });

        return view('selesai', [
            'items' => $items,
            'filter' => session('filter', 'asli'),
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

        $first = collect(session('photos', []))->flatten()->first();
        $name = 'fotobooth-'.time().'.jpg';

        return response()->streamDownload(function () use ($first) {
            echo file_get_contents(base_path('public'.parse_url($first, PHP_URL_PATH)));
        }, $name, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * Item cart (frame unik berurutan sesuai pilihan user)
     * lengkap dengan harga, qty, jumlah foto, dan slot frame.
     */
    private function cartItems(): array
    {
        $cart = session('cart', []);
        if (! $cart) {
            return [];
        }

        $frames = Frame::whereIn('slug', array_keys($cart))->get()->keyBy('slug');

        $items = [];
        foreach ($cart as $slug => $qty) {
            if (! $frames->has($slug)) {
                continue;
            }
            $frame = $frames->get($slug);
            $items[] = [
                'slug' => $slug,
                'name' => $frame->name,
                'qty' => (int) $qty,
                'price' => (int) $frame->price,
                'photo_count' => max(1, (int) $frame->photo_count),
                'image_url' => $frame->image_url,
                'slots' => $frame->slots ?? [],
                'caption' => $frame->caption,
            ];
        }

        return $items;
    }

    private function total(): int
    {
        return array_sum(array_map(
            fn ($item) => $item['price'] * $item['qty'],
            $this->cartItems()
        ));
    }

    private function maxPhotoCount(): int
    {
        return array_sum(array_map(
            fn ($item) => $item['photo_count'],
            $this->cartItems()
        ));
    }

    /**
     * Bersihkan data sesi yang tidak lagi valid.
     */
    private function resetCartSession(Request $request, array $keep = []): void
    {
        $forget = ['cart', 'photos', 'filter', 'metode', 'paid', 'email'];
        $request->session()->forget(array_diff($forget, $keep));
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
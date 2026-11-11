<?php

namespace App\Http\Controllers;

use App\Mail\PhotoboothMail;
use App\Models\ActivityLog;
use App\Models\Frame;
use App\Models\Setting;
use App\Models\Voucher;
use App\Support\Brand;
use App\Support\QueueNumber;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    /** Durasi default seleksi (metode, frame, pembayaran) sebelum bayar (detik). */
    private const DEFAULT_SELECTION_SECONDS = 600;

    /** Durasi default sesi foto setelah pembayaran (detik). */
    private const DEFAULT_PHOTO_SECONDS = 300;

    /** Durasi default review, cetak, kirim email setelah foto (detik). */
    private const DEFAULT_REVIEW_SECONDS = 300;

    public function welcome(Request $request)
    {
        // Sesi lama yang waktunya habis dibersihkan saat kembali ke layar awal.
        if ($request->session()->has('queue') && $this->remainingSeconds() <= 0) {
            $this->cleanupSession($request);
        }

        $activeFrames = Frame::active()->get();

        return view('welcome', [
            'frameCount' => $activeFrames->count(),
            'minPrice' => (int) $activeFrames->min('price'),
            'durationMinutes' => (int) round($this->sessionSelectionSeconds() / 60),
        ]);
    }

    /**
     * Kanvas SPA — 1 dokumen penuh berisi SEMUA panel sekaligus.
     * Alur boot: '/' (Mulai) -> POST /mulai -> /tutorial -> /spa.
     * Panel yang tampil ditentukan server via ?step= / session spa_step
     * (default 'frame') agar tiap tahap selalu ter-render penuh dengan data sesi.
     */
    /**
     * Versi aset build aktif (untuk deteksi tab basi di klien).
     */
    public function version()
    {
        $manifest = json_decode(@file_get_contents(public_path('build/manifest.json')), true) ?: [];
        $js = $manifest['resources/js/app.js']['file'] ?? null;
        $css = $manifest['resources/css/app.css']['file'] ?? null;

        return response()->json([
            'js' => $js ? basename($js) : null,
            'css' => $css ? basename($css) : null,
        ])->header('Cache-Control', 'no-store, max-age=0');
    }

    public function spa(Request $request)
    {
        // Jangan biarkan sesi yang sudah kedaluwarsa tetap membuka panel kiosk.
        if ($request->session()->has('queue') && $this->remainingSeconds() <= 0) {
            $this->cleanupSession($request);

            return redirect()->route('home')->with('expired', true);
        }

        if (! $request->session()->has('queue')) {
            $queue = QueueNumber::next();
            $request->session()->put('queue', $queue);
            $request->session()->put('started_at', now()->timestamp);
            $request->session()->put('total_seconds', $this->sessionSelectionSeconds());
            $this->resetCartSession($request);
        }

        $metode = (string) session('metode', '');
        $paid = $request->session()->has('paid');
        $hasCart = ! empty(session('cart', []));
        $hasPhotos = ! empty(session('photos', []));

        $validSteps = ['metode', 'frame', 'pembayaran', 'foto', 'review'];
        $available = [
            'metode' => ! $paid,
            'frame' => $metode === 'qris' && ! $paid,
            'pembayaran' => $metode === 'qris' && ! $paid && $hasCart,
            'foto' => $hasCart && ($paid || $metode === 'voucher'),
            'review' => $paid && $hasPhotos,
        ];

        $requested = (string) $request->query('step', $request->session()->get('spa_step', 'metode'));
        if (! in_array($requested, $validSteps, true)) {
            $requested = 'metode';
        }

        $step = ($available[$requested] ?? false)
            ? $requested
            : $this->defaultSpaStep($metode, $paid, $hasCart, $hasPhotos);

        // Jangan biarkan permintaan melompat ke tahap yang datanya belum ada.
        if (! ($available[$step] ?? false)) {
            $step = $this->defaultSpaStep($metode, $paid, $hasCart, $hasPhotos);
        }

        $request->session()->put('spa_step', $step);

        $allFrames = Frame::active()->orderBy('sort_order')->get()->keyBy('slug');
        $categories = Frame::active()->orderBy('sort_order')->get()
            ->pluck('category')->unique()->filter()->values()->toArray();

        $photos = collect(session('photos', []))->flatten()->values()->all();

        $items = collect($this->cartItems())->map(function ($item) use ($photos) {
            $item['photos'] = session('photos', [])[$item['slug']] ?? [];
            $item['filter_map'] = $this->itemFilterMap($item['slug'], $item['photos']);

            return $item;
        })->values()->all();

        return response()->view('layouts.spa', [
            'queue' => session('queue'),
            'step' => $step,
            'flowSteps' => $this->flowSteps($metode),
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
            'metode' => $metode,
            'voucher' => session('voucher'),
            'nmid' => Setting::get('qris_nmid', '102020034073193'),
            'merchant' => Setting::get('qris_merchant', 'PERKAKASKU'),
            'qris_image' => Setting::get('qris_image'),
            'needs_pin' => ! empty(Setting::get('cashier_pin_hash')),
            'cam_width' => (int) Setting::get('cam_width', '1024'),
            'cam_height' => (int) Setting::get('cam_height', '768'),
            'countdown' => (int) Setting::get('countdown', '3'),
        ])->header('Cache-Control', 'no-store, max-age=0');
    }

    public function mulai(Request $request)
    {
        $this->deletePhotos($request->session()->getId());
        $request->session()->regenerate();
        $queue = QueueNumber::next();

        $request->session()->put('queue', $queue);
        $request->session()->put('started_at', now()->timestamp);
        $request->session()->put('total_seconds', $this->sessionSelectionSeconds());
        $request->session()->put('spa_step', 'metode');

        $this->resetCartSession($request);

        return redirect()->route('tutorial');
    }

    public function tutorial(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        return view('tutorial', [
            'queue' => session('queue'),
            'step' => 1,
            'durationMinutes' => (int) round($this->sessionSelectionSeconds() / 60),
        ]);
    }

    public function frame(Request $request)
    {
        $this->requirePrereq($request, ['metode']);

        if (session('metode') === 'voucher') {
            return redirect()->route('foto');
        }

        if ($request->session()->has('paid')) {
            return redirect()->route('foto');
        }

        $allFrames = Frame::active()->orderBy('sort_order')->get()->keyBy('slug');
        $categories = Frame::active()->orderBy('sort_order')->get()->pluck('category')->unique()->filter()->values()->toArray();

        return view('frame', [
            'categories' => $categories,
            'allFrames' => $allFrames,
            'cart' => session('cart', []),
            'metode' => session('metode'),
            'voucher' => session('voucher'),
            'step' => 2,
        ]);
    }

    public function frameStore(Request $request)
    {
        $this->requirePrereq($request, ['metode']);

        if (session('metode') !== 'qris' || $request->session()->has('paid')) {
            return redirect()->route('foto');
        }

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
        // Frame/key foto berubah => hasil foto dan pembayaran lama tidak berlaku.
        $this->resetCartSession($request, ['cart', 'metode']);

        if ($request->boolean('from_spa')) {
            $request->session()->put('spa_step', 'pembayaran');

            return redirect()->route('spa', ['step' => 'pembayaran']);
        }

        return redirect()->route('pembayaran');
    }

    public function foto(Request $request)
    {
        $this->requirePrereq($request, ['cart', 'metode']);

        if (! $request->session()->has('paid')) {
            return redirect()->route('home');
        }

        // Mulai atau perpanjang timer khusus untuk sesi foto.
        $request->session()->put('started_at', now()->timestamp);
        $request->session()->put('total_seconds', $this->sessionPhotoSeconds());

        return view('foto', [
            'queue' => session('queue'),
            'items' => $this->cartItems(),
            'photoCount' => $this->maxPhotoCount(),
            'metode' => session('metode'),
            'voucher' => session('voucher'),
            'step' => 3,
        ]);
    }

    public function fotoStore(Request $request)
    {
        $this->requirePrereq($request, ['cart', 'metode']);

        if (! $request->session()->has('paid')) {
            return response()->json(['message' => 'Pembayaran belum dikonfirmasi.'], 422);
        }

        $photoCount = $this->maxPhotoCount();

        $data = $request->validate([
            'photos' => ['required', 'array', 'size:'.$photoCount],
            'photos.*' => [
                'required',
                'string',
                'max:3000000',
                // Hanya data URL gambar yang boleh masuk; file lain ditolak
                // sebelum sempat ditulis ke disk.
                'regex:/^data:image\/(?:jpe?g|png|webp);base64,[A-Za-z0-9+\/=\r\n]+$/',
            ],
        ]);

        $path = 'photos/'.$request->session()->getId();
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

        // Validasi semua foto lebih dulu supaya tidak ada sesi setengah jadi
        // saat salah satu foto ternyata rusak.
        $binaries = [];
        foreach ($data['photos'] as $index => $raw) {
            $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $raw);
            $binary = base64_decode($base64, true);

            if ($binary === false || $binary === '' || strlen($binary) > 8 * 1024 * 1024) {
                return response()->json(['message' => 'Foto tidak valid atau terlalu besar.'], 422);
            }

            $info = @getimagesizefromstring($binary);
            if (! $info || ! in_array($info[2], $allowedTypes, true)) {
                return response()->json(['message' => 'Format foto tidak didukung.'], 422);
            }

            $binaries[$index] = $binary;
        }

        Storage::disk('public')->deleteDirectory($path);

        $saved = [];
        $index = 0;
        foreach ($this->cartItems() as $item) {
            $savedItem = [];
            foreach (range(1, $item['photo_count']) as $key) {
                $filename = 'foto-'.($index + 1).'.jpg';
                Storage::disk('public')->put($path.'/'.$filename, $binaries[$index]);
                // gunakan URL relatif-akar agar bekerja di mana pun kiosk diakses
                $savedItem[] = '/storage/'.$path.'/'.$filename;
                $index++;
            }
            $saved[$item['slug']] = $savedItem;
        }

        $request->session()->put('photos', $saved);

        // Arsip admin tidak boleh menghalangi pelanggan menyelesaikan sesi.
        try {
            $this->archivePhotos($request, $saved);
        } catch (\Throwable $error) {
            report($error);
        }
        // Pastikan masih ada waktu untuk review, cetak, unduh, dan kirim email.
        $remaining = $this->remainingSeconds();
        $request->session()->put('started_at', now()->timestamp);
        $request->session()->put('total_seconds', max($remaining, $this->sessionReviewSeconds()));
        $request->session()->put('spa_step', 'review');

        $redirect = $request->boolean('from_spa')
            ? route('spa', ['step' => 'review'])
            : route('review');

        return response()->json(['redirect' => $redirect]);
    }

    /**
     * Rute lama /filter tetap ada agar tautan/tes lama tidak error, tetapi
     * tahap filter sudah dihapus dari alur kiosk. Permintaan diarahkan ke
     * tahap berikutnya sesuai data sesi.
     */
    public function filter(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        return redirect()->route('spa', ['step' => $this->defaultSpaStep(
            (string) session('metode', ''),
            $request->session()->has('paid'),
            ! empty(session('cart', [])),
            ! empty(session('photos', []))
        )]);
    }

    public function filterStore(Request $request)
    {
        return $this->filter($request);
    }

    public function metode(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        if ($request->session()->has('paid')) {
            return redirect()->route('foto');
        }

        return view('metode', [
            'metode' => session('metode'),
            'voucher' => session('voucher'),
            'total' => $this->total(),
            'step' => 1,
        ]);
    }

    public function metodeStore(Request $request)
    {
        $this->requirePrereq($request, ['queue']);

        $data = $request->validate([
            'metode' => ['required', 'in:qris,voucher'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ]);

        if ($data['metode'] === 'qris') {
            $this->resetCartSession($request);
            $request->session()->put('metode', 'qris');
            $request->session()->put('spa_step', 'frame');

            if ($request->boolean('from_spa')) {
                return redirect()->route('spa', ['step' => 'frame']);
            }

            return redirect()->route('frame');
        }

        $code = strtoupper(trim((string) ($data['voucher_code'] ?? '')));
        if ($code === '') {
            return back()->withErrors(['voucher_code' => 'Masukkan kode voucher terlebih dahulu.'])->withInput();
        }

        // Klik ganda dengan kode yang sama tidak boleh memotong kuota dua kali.
        if (session('voucher.code') === $code && $request->session()->has('paid')) {
            return $request->boolean('from_spa')
                ? redirect()->route('spa', ['step' => 'foto'])
                : redirect()->route('foto');
        }

        $result = DB::transaction(function () use ($code) {
            $voucher = Voucher::where('code', $code)->lockForUpdate()->first();

            if (! $voucher || ! $voucher->is_active) {
                return ['error' => 'Kode voucher tidak ditemukan atau sudah tidak aktif.'];
            }
            if ($voucher->isExpired()) {
                return ['error' => 'Voucher sudah kedaluwarsa.'];
            }
            if ($voucher->isExhausted()) {
                return ['error' => 'Kuota voucher ini sudah habis.'];
            }

            $requested = is_array($voucher->items) ? $voucher->items : [];
            $frames = Frame::active()->whereIn('slug', array_keys($requested))->get()->keyBy('slug');

            $cart = [];
            foreach ($requested as $slug => $qty) {
                if ($frames->has($slug) && (int) $qty > 0) {
                    $cart[$slug] = (int) $qty;
                }
            }

            if (! $cart) {
                return ['error' => 'Frame pada voucher ini sudah tidak tersedia. Hubungi admin.'];
            }

            $voucher->increment('used_count');

            return ['voucher' => $voucher, 'cart' => $cart];
        });

        if (isset($result['error'])) {
            return back()->withErrors(['voucher_code' => $result['error']])->withInput();
        }

        /** @var Voucher $voucher */
        $voucher = $result['voucher'];

        $oldRemaining = $this->remainingSeconds();
        $this->resetCartSession($request);
        $request->session()->put('metode', 'voucher');
        $request->session()->put('cart', $result['cart']);
        $request->session()->put('paid', true);
        $request->session()->put('voucher', [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'name' => $voucher->name,
        ]);
        $request->session()->put('voucher_value', $this->total());
        $request->session()->put('spa_step', 'foto');

        // Voucher sudah dibayar di awal; beri sisa waktu yang cukup untuk
        // sesi foto + review tanpa memperpanjang tanpa batas.
        $request->session()->put('started_at', now()->timestamp);
        $request->session()->put('total_seconds', max($oldRemaining, $this->sessionReviewSeconds()));

        $this->logPayment(0, [
            'voucher_code' => $voucher->code,
            'voucher_value' => $this->total(),
        ]);

        if ($request->boolean('from_spa')) {
            return redirect()->route('spa', ['step' => 'foto']);
        }

        return redirect()->route('foto');
    }

    public function pembayaran(Request $request)
    {
        $this->requirePrereq($request, ['metode', 'cart']);

        if (session('metode') !== 'qris') {
            return redirect()->route('foto');
        }

        if ($request->session()->has('paid')) {
            return redirect()->route('foto');
        }

        return view('pembayaran', [
            'metode' => session('metode'),
            'total' => $this->total(),
            'nmid' => Setting::get('qris_nmid', '102020034073193'),
            'merchant' => Setting::get('qris_merchant', 'PERKAKASKU'),
            'qris_image' => Setting::get('qris_image'),
            'queue' => session('queue'),
            'needs_pin' => ! empty(Setting::get('cashier_pin_hash')),
            'step' => 3,
        ]);
    }

    public function pembayaranStore(Request $request)
    {
        $this->requirePrereq($request, ['metode', 'cart']);

        if (session('metode') !== 'qris') {
            return redirect()->route('foto');
        }

        // Idempoten: klik ganda / refresh tidak boleh mencatat pembayaran dua kali.
        if (! $request->session()->has('paid')) {
            // PIN kasir (opsional, diatur admin): kasir memverifikasi uang/QRIS
            // sudah masuk, lalu memasukkan PIN. Kosong = konfirmasi bebas.
            $pinHash = Setting::get('cashier_pin_hash');
            if (! empty($pinHash)) {
                $pin = (string) $request->input('pin', '');
                if ($pin === '' || ! Hash::check($pin, $pinHash)) {
                    return back()->withErrors(['pin' => 'PIN kasir salah. Minta kasir memverifikasi pembayaranmu.']);
                }
            }

            $remaining = $this->remainingSeconds();
            $request->session()->put('paid', true);
            // Pembayaran kini terjadi sebelum sesi foto. Waktu untuk sesi foto
            // dan review diatur terpisah di Pengaturan.
            $request->session()->put('started_at', now()->timestamp);
            $request->session()->put('total_seconds', max($remaining, $this->sessionReviewSeconds()));

            $this->logPayment();
        }

        $request->session()->put('spa_step', 'foto');

        if ($request->boolean('from_spa')) {
            return redirect()->route('spa', ['step' => 'foto']);
        }

        return redirect()->route('foto');
    }

    public function review(Request $request)
    {
        $this->requirePrereq($request, ['paid', 'photos']);

        // Perpanjang timer khusus untuk tahap review, cetak, dan kirim email.
        $request->session()->put('started_at', now()->timestamp);
        $request->session()->put('total_seconds', $this->sessionReviewSeconds());

        $photos = session('photos', []);

        $items = collect($this->cartItems())->map(function ($item) use ($photos) {
            $item['photos'] = $photos[$item['slug']] ?? [];
            $item['filter_map'] = $this->itemFilterMap($item['slug'], $item['photos']);

            return $item;
        });

        return view('review', [
            'items' => $items,
            'filter' => session('filter', 'asli'),
            'total' => $this->total(),
            'queue' => session('queue'),
            'metode' => session('metode'),
            'voucher' => session('voucher'),
            'step' => 4,
        ]);
    }

    public function emailStore(Request $request)
    {
        $this->requirePrereq($request, ['paid']);

        $data = $request->validate([
            'email' => ['required', 'email:rfc'],
            // Strip hasil komposisi (JPEG data URL) dari kanvas klien, dilampirkan ke email.
            'strips' => ['nullable', 'array', 'max:20'],
            'strips.*' => [
                'string',
                'max:8000000',
                'regex:/^data:image\/(?:jpe?g|png|webp);base64,[A-Za-z0-9+\/=\r\n]+$/',
            ],
        ]);

        // Konfigurasi dinamis SMTP dari database Settings (jika diatur oleh Admin)
        $mailHost = Setting::get('mail_host');
        if (! empty($mailHost)) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $mailHost,
                'mail.mailers.smtp.port' => (int) Setting::get('mail_port', '587'),
                'mail.mailers.smtp.username' => Setting::get('mail_username'),
                'mail.mailers.smtp.password' => Setting::get('mail_password'),
                'mail.mailers.smtp.encryption' => Setting::get('mail_encryption') === 'null' ? null : Setting::get('mail_encryption', 'tls'),
                'mail.from.address' => Setting::get('mail_from_address', Setting::get('mail_username')),
                'mail.from.name' => Setting::get('mail_from_name', 'Photobooth Studio'),
            ]);
        }

        $attachments = [];
        foreach ($data['strips'] ?? [] as $strip) {
            $raw = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $strip), true);
            if ($raw !== false && $raw !== '' && strlen($raw) <= 6 * 1024 * 1024 && @getimagesizefromstring($raw)) {
                $attachments[] = $raw;
            }
        }

        try {
            Mail::to($data['email'])->send(new PhotoboothMail(session('queue'), $attachments));
        } catch (\Throwable $e) {
            report($e);

            // Jangan pura-pura berhasil: kembalikan error agar kiosk bisa menampilkannya.
            return response()->json([
                'message' => 'Email gagal dikirim. Periksa alamat email lalu coba lagi, atau unduh softfile langsung.',
            ], 422);
        }

        $request->session()->put('email', $data['email']);

        // Tetap di halaman review agar pelanggan masih bisa mencetak/mengunduh
        // setelah mengirim email (dulu langsung dilempar ke halaman selesai).
        return response()->json([
            'ok' => true,
            'email' => $data['email'],
            'message' => 'Softfile berhasil dikirim ke '.$data['email'].'.',
        ]);
    }

    public function selesai(Request $request)
    {
        $this->requirePrereq($request, ['paid']);

        $photos = session('photos', []);

        $items = collect($this->cartItems())->map(function ($item) use ($photos) {
            $item['photos'] = $photos[$item['slug']] ?? [];
            $item['filter_map'] = $this->itemFilterMap($item['slug'], $item['photos']);

            return $item;
        });

        return view('selesai', [
            'items' => $items,
            'filter' => session('filter', 'asli'),
            'email' => session('email'),
            'queue' => session('queue'),
            'metode' => session('metode'),
            'voucher' => session('voucher'),
            'step' => 5,
        ]);
    }

    public function selesaiStore(Request $request)
    {
        // Foto pelanggan tidak boleh tertinggal di server setelah sesi selesai.
        $this->deletePhotos($request->session()->getId());
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect()->route('home')->with('finished', true);
    }

    public function download(Request $request)
    {
        $this->requirePrereq($request, ['photos']);

        $first = collect(session('photos', []))->flatten()->first();
        $path = $first ? base_path('public'.parse_url($first, PHP_URL_PATH)) : null;

        abort_unless($path && is_file($path), 404, 'Softfile tidak ditemukan.');

        $name = 'fotobooth-'.time().'.jpg';

        return response()->streamDownload(function () use ($path) {
            readfile($path);
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

    /**
     * Filter per-foto: session 'filters' berformat ['slug:index' => key].
     * Jatuh kembali ke filter global sesi lalu 'asli' (kompatibel alur lama).
     */
    private function photoFilter(string $slug, int $index): string
    {
        $map = session('filters', []);
        $key = (is_array($map) && array_key_exists($slug.':'.$index, $map))
            ? $map[$slug.':'.$index]
            : session('filter', 'asli');

        return array_key_exists($key, self::FILTERS) ? $key : 'asli';
    }

    /**
     * Peta filter per foto lokal sebuah item: [localIndex => key].
     */
    private function itemFilterMap(string $slug, array $photos): array
    {
        $map = [];
        foreach (array_values($photos) as $i => $photo) {
            $map[$i] = $this->photoFilter($slug, $i);
        }

        return $map;
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
        $forget = ['cart', 'photos', 'filter', 'filters', 'metode', 'paid', 'email', 'voucher', 'voucher_value', 'payment_log_id'];
        $request->session()->forget(array_diff($forget, $keep));
    }

    /**
     * Tentukan tahap SPA yang paling masuk akal dari data sesi saat ini.
     * Dipakai untuk menjepit permintaan step yang belum memenuhi prasyarat.
     */
    private function defaultSpaStep(string $metode, bool $paid, bool $hasCart, bool $hasPhotos): string
    {
        if ($paid && $hasPhotos) {
            return 'review';
        }

        if ($paid && $hasCart) {
            return 'foto';
        }

        if ($metode === 'qris') {
            return $hasCart ? 'pembayaran' : 'frame';
        }

        if ($metode === 'voucher') {
            return $hasCart ? 'foto' : 'metode';
        }

        return 'metode';
    }

    /**
     * Urutan stepper yang tampil di kiosk. Voucher melewati frame dan
     * konfirmasi QRIS karena isi keranjang sudah ditentukan voucher.
     */
    private function flowSteps(string $metode): array
    {
        $brand = Brand::get();

        if ($metode === 'voucher') {
            return [
                ['key' => 'metode', 'label' => $brand['step_voucher']],
                ['key' => 'foto', 'label' => $brand['step_photo']],
                ['key' => 'review', 'label' => $brand['step_review']],
            ];
        }

        if ($metode === 'qris') {
            return [
                ['key' => 'metode', 'label' => $brand['step_method']],
                ['key' => 'frame', 'label' => $brand['step_frame']],
                ['key' => 'pembayaran', 'label' => $brand['step_payment']],
                ['key' => 'foto', 'label' => $brand['step_photo']],
                ['key' => 'review', 'label' => $brand['step_review']],
            ];
        }

        // Sebelum metode dipilih, tampilkan alur QRIS (pilihan default) agar
        // stepper tidak berubah panjang saat pelanggan menekan QRIS.
        return [
            ['key' => 'metode', 'label' => $brand['step_choose']],
            ['key' => 'frame', 'label' => $brand['step_frame']],
            ['key' => 'pembayaran', 'label' => $brand['step_payment']],
            ['key' => 'foto', 'label' => $brand['step_photo']],
            ['key' => 'review', 'label' => $brand['step_review']],
        ];
    }

    /**
     * Pastikan alur dilalui secara berurutan.
     * Setiap halaman hanya bisa diakses jika data prasyaratnya sudah ada dan
     * waktu sesi belum habis (dihitung dari server, bukan dari jam klien).
     */
    private function requirePrereq(Request $request, array $keys): void
    {
        if ($request->session()->has('queue') && $this->remainingSeconds() <= 0) {
            $this->cleanupSession($request);

            throw new HttpResponseException(
                redirect()->route('home')->with('expired', true)
            );
        }

        $missing = ! $request->session()->has('queue')
            ? ['queue']
            : array_filter($keys, fn (string $key) => ! $request->session()->has($key));

        if ($missing) {
            throw new HttpResponseException(redirect()->route('home'));
        }
    }

    /**
     * Satu-satunya yang dicatat ke log: pembayaran yang dikonfirmasi.
     */
    private function logPayment(?int $totalOverride = null, array $extra = []): void
    {
        $items = array_map(fn (array $item) => [
            'name' => $item['name'],
            'qty' => $item['qty'],
            'price' => $item['price'],
        ], $this->cartItems());

        $log = ActivityLog::create([
            'session_id' => session()->getId(),
            'queue_number' => session('queue'),
            'action' => ActivityLog::PAYMENT,
            'metadata' => array_merge([
                'metode' => session('metode'),
                'total' => $totalOverride ?? $this->total(),
                'photo_count' => $this->maxPhotoCount(),
                'items' => $items,
                'ip' => request()->ip(),
            ], $extra),
        ]);

        session()->put('payment_log_id', $log->id);
    }

    /**
     * Durasi tahap seleksi (metode, frame, pembayaran) sebelum pembayaran.
     */
    private function sessionSelectionSeconds(): int
    {
        $seconds = (int) Setting::get('session_selection_seconds', (string) self::DEFAULT_SELECTION_SECONDS);

        return max(60, min(3600, $seconds ?: self::DEFAULT_SELECTION_SECONDS));
    }

    /**
     * Durasi sesi foto setelah pembayaran dikonfirmasi.
     */
    private function sessionPhotoSeconds(): int
    {
        $seconds = (int) Setting::get('session_photo_seconds', (string) self::DEFAULT_PHOTO_SECONDS);

        return max(60, min(3600, $seconds ?: self::DEFAULT_PHOTO_SECONDS));
    }

    /**
     * Durasi tahap review, cetak, unduh, dan kirim email setelah foto selesai.
     */
    private function sessionReviewSeconds(): int
    {
        $seconds = (int) Setting::get('session_review_seconds', (string) self::DEFAULT_REVIEW_SECONDS);

        return max(60, min(3600, $seconds ?: self::DEFAULT_REVIEW_SECONDS));
    }

    /**
     * Kompatibilitas lama: durasi seleksi (digunakan welcome dan tutorial).
     */
    private function sessionSeconds(): int
    {
        return $this->sessionSelectionSeconds();
    }

    /**
     * Sisa waktu sesi (detik) dihitung dari server, sehingga tidak
     * ter-reset tiap kali panel dimuat ulang.
     */
    private function remainingSeconds(): int
    {
        $total = (int) session('total_seconds', self::DEFAULT_SELECTION_SECONDS);
        $elapsed = now()->timestamp - (int) session('started_at', now()->timestamp);

        return max(0, $total - $elapsed);
    }

    /**
     * Simpan salinan permanen foto + detail frame ke folder arsip milik log
     * pembayaran. Folder sesi tetap dihapus saat sesi selesai, sedangkan arsip
     * ini dipakai admin untuk melihat dan mengunduh hasil foto pelanggan.
     */
    private function archivePhotos(Request $request, array $saved): void
    {
        $logId = (int) session('payment_log_id', 0);
        $log = $logId > 0
            ? ActivityLog::payments()->whereKey($logId)->first()
            : null;

        // Bila log yang tersimpan sudah dihapus admin, pakai pembayaran
        // terakhir di sesi ini sebagai jaring pengaman.
        if (! $log) {
            $log = ActivityLog::payments()
                ->forSession($request->session()->getId())
                ->latest('id')
                ->first();
        }

        if (! $log) {
            return;
        }

        $sessionId = $request->session()->getId();
        $existingArchive = $log->metadata['archive_dir'] ?? null;
        $directory = is_string($existingArchive) && str_starts_with($existingArchive, 'arsip/')
            ? $existingArchive
            : 'arsip/'.$log->id.'-'.bin2hex(random_bytes(12));

        Storage::disk('public')->deleteDirectory($directory);

        $archived = [];
        foreach ($saved as $slug => $urls) {
            foreach ($urls as $index => $url) {
                $name = basename((string) parse_url($url, PHP_URL_PATH));
                if ($name === '') {
                    continue;
                }

                $source = 'photos/'.$sessionId.'/'.$name;
                if (! Storage::disk('public')->exists($source)) {
                    continue;
                }

                Storage::disk('public')->copy($source, $directory.'/'.$name);
                $archived[$slug][$index] = '/storage/'.$directory.'/'.$name;
            }
        }

        $items = collect($this->cartItems())->map(function (array $item) use ($archived, $directory) {
            $photos = array_values($archived[$item['slug']] ?? []);
            $frameUrl = $item['image_url'];

            // Ikutkan gambar frame ke arsip agar hasil lama tetap utuh
            // walaupun frame diubah atau dihapus dari admin.
            if (is_string($frameUrl) && $frameUrl !== '') {
                $framePath = parse_url($frameUrl, PHP_URL_PATH);
                $framePath = is_string($framePath) ? ltrim($framePath, '/') : '';
                if (str_starts_with($framePath, 'storage/')) {
                    $framePath = substr($framePath, strlen('storage/'));
                }
                if ($framePath !== '' && Storage::disk('public')->exists($framePath)) {
                    $extension = pathinfo($framePath, PATHINFO_EXTENSION) ?: 'png';
                    $name = preg_replace('/[^a-z0-9_-]+/i', '-', $item['slug']) ?: 'frame';
                    $target = $directory.'/frame-'.$name.'.'.$extension;
                    Storage::disk('public')->copy($framePath, $target);
                    $frameUrl = '/storage/'.$target;
                }
            }

            return [
                'slug' => $item['slug'],
                'name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'photo_count' => $item['photo_count'],
                'image_url' => $frameUrl,
                'slots' => $item['slots'],
                'caption' => $item['caption'],
                'photos' => $photos,
                'filters' => array_values($this->itemFilterMap($item['slug'], $photos)),
            ];
        })->all();

        $metadata = $log->metadata ?? [];
        $metadata['items'] = $items;
        $metadata['photos'] = $archived;
        $metadata['archive_dir'] = $directory;
        $metadata['filters'] = session('filters', []);
        $metadata['archived_at'] = now()->toIso8601String();

        $log->metadata = $metadata;
        $log->save();
    }

    private function deletePhotos(string $sessionId): void
    {
        Storage::disk('public')->deleteDirectory('photos/'.$sessionId);
    }

    private function cleanupSession(Request $request): void
    {
        $this->deletePhotos($request->session()->getId());
        $request->session()->flush();
        $request->session()->regenerate();
    }
}
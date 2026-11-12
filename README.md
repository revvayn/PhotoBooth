# Photobooth

Aplikasi photobooth berbasis Laravel + Vite. Pelanggan memilih frame, membayar (QRIS/voucher), mengambil foto, lalu mencetak/mengunduh/mengirim hasil. Admin mengelola frame, voucher, log pembayaran, tampilan, dan pengaturan kiosk.

## Fitur Utama

- Sesi photobooth: metode pembayaran, pilih frame, pembayaran, foto, review/cetak/unduh/email.
- Frame gallery dengan kategori ukuran `2R`, `4R`, `A4`.
- Voucher cash dan event dengan wizard pembuatan.
- Voucher event: satu kode, pemakaian berdasarkan jumlah orang atau durasi jam, dengan rentang jam berlaku.
- Log pembayaran: arsip foto, preview komposit, unduh ZIP, filter tanggal/metode, hapus per filter.
- Nomor antrian harian format `#DDMMYY-XXX`.
- Admin: frame, voucher, log pembayaran, tampilan brand, pengaturan kiosk (QRIS, kamera, durasi sesi, email, PIN kasir).
- Notifikasi toast untuk aksi admin.

## Kebutuhan

- PHP 8.2+
- Composer
- Node.js + npm
- Database (SQLite/MySQL, sesuai `.env`)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
```

## Menjalankan Aplikasi

Development:

```bash
php artisan serve
npm run dev
```

Production build:

```bash
npm run build
php artisan view:cache
```

## Login Admin

- URL: `/admin/login`
- Email default: `admin@photobooth.com`
- Password default: `password`

> Ganti kredensial default setelah instalasi.

## Alur Pelanggan

1. Buka halaman utama, tekan **Mulai**.
2. Pilih metode: QRIS atau Voucher.
3. Jika QRIS: pilih frame, konfirmasi pembayaran.
4. Jika Voucher: masukkan kode, langsung sesi foto.
5. Ambil foto sesuai frame.
6. Review, cetak, unduh, atau kirim email.

## Testing

```bash
php artisan test
npm run build
```

## Struktur Penting

- `app/Http/Controllers/PhotoboothController.php` - alur kiosk.
- `app/Http/Controllers/Admin/` - admin frame, voucher, log, pengaturan, tampilan.
- `resources/views/partials/` - panel kiosk (metode, frame, pembayaran, foto, review).
- `resources/views/admin/` - halaman admin.
- `database/migrations/` - skema database, termasuk `queue_counters`.

## Catatan

- Folder arsip foto disimpan di `storage/app/public/arsip`.
- Voucher event dibuat sebagai satu kode; pemakaian mengikuti jumlah orang atau durasi.
- Penghapusan log/voucher berdasarkan filter memerlukan minimal satu filter aktif.
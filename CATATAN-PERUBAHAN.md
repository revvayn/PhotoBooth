# Catatan perubahan

Timpa file di proyekmu dengan file di zip ini (struktur folder sama), lalu:

    npm run build        # WAJIB: kelas Tailwind baru harus di-compile ulang
    php artisan test     # opsional, ada tes baru: tests/Feature/PaymentLogTest.php

Tidak ada migrasi database baru.

## Alur kiosk
- Timer sesi tidak lagi ke-reset di tiap langkah (dihitung dari server). Durasi bisa diatur di Admin > Pengaturan.
- Setelah bayar, pelanggan dapat 5 menit baru untuk cetak/kirim dan tidak bisa kembali mengubah pesanan.
- Tombol Kembali di tiap langkah (Ganti Frame, Foto Ulang, Kembali, Ganti metode).
- Kamera hanya menyala di langkah Foto (dulu menyala sejak langkah Frame).
- Konfirmasi bayar tidak bisa dobel (klik ganda/refresh). PIN kasir opsional di Pengaturan.
- Review: tombol Cetak (sesuai jumlah lembar yang dibayar), Unduh, dan kirim email dengan foto terlampir.
- Email yang gagal kini menampilkan pesan error (dulu tetap dianggap berhasil).
- Foto pelanggan dihapus dari server saat sesi selesai / habis.
- QR invoice memakai nomor antrian yang benar.

## Admin
- Log hanya mencatat pembayaran (aktivitas lain, termasuk aksi admin, tidak dicatat lagi).
- Dashboard: pendapatan hari ini/total, per metode, grafik 7 hari, pembayaran terbaru.
- Log Pembayaran: filter (antrian, metode, tanggal), urut kolom, total ringkasan, ekspor CSV.
- Pengaturan: durasi sesi, PIN kasir; password SMTP tidak lagi tampil di HTML.
- Login dibatasi 6 percobaan/menit; pesan error bahasa Indonesia.
- Daftar frame: kolom harga dan urutan.

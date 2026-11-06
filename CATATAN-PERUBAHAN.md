# Catatan perubahan

Timpa file di proyekmu dengan file di zip ini (struktur folder sama), lalu:

    php artisan migrate  # WAJIB: tabel vouchers baru
    npm run build        # WAJIB: kelas Tailwind baru harus di-compile ulang
    php artisan test     # opsional, ada tes baru untuk voucher, tampilan, dan alur kiosk

## Alur kiosk
- Metode pembayaran (QRIS atau Voucher) dipilih di awal, sebelum frame.
- QRIS: pilih frame -> konfirmasi/scan QRIS -> sesi foto. Total mengikuti frame yang dipilih.
- Voucher: masukkan kode -> frame dan jumlah cetak otomatis -> langsung sesi foto. Pelanggan tidak bisa memilih frame/jumlah lagi.
- Langkah filter dihapus dari alur dan stepper. Rute lama `/filter` tetap ada sebagai pengalihan agar tautan lama tidak error.
- Timer sesi tidak lagi ke-reset di tiap langkah (dihitung dari server). Durasi bisa diatur di Admin > Pengaturan.
- Pembayaran QRIS dilakukan sebelum sesi foto, dan setelah foto selesai pelanggan tetap mendapat waktu untuk cetak/kirim.
- Tombol Kembali di tiap langkah (Ganti metode, Ganti Frame, Foto Ulang).
- Kamera hanya menyala di langkah Foto.
- Konfirmasi bayar tidak bisa dobel (klik ganda/refresh). PIN kasir opsional di Pengaturan.
- Review: tombol Cetak (sesuai jumlah lembar yang dibayar), Unduh, dan kirim email dengan foto terlampir.
- Email yang gagal kini menampilkan pesan error (dulu tetap dianggap berhasil).
- Foto pelanggan dihapus dari server saat sesi selesai / habis.
- QR invoice memakai nomor antrian yang benar.

## Admin
- Voucher: buat/edit/hapus kode, pilih frame + jumlah cetak, batas pemakaian, masa berlaku, dan status aktif.
- Tampilan: ubah nama brand, tagline, teks sambutan/footer, logo, gambar latar, serta warna utama/sekunder/aksen/gradien kiosk.
- Warna brand diterapkan lewat CSS variable dan override kelas Tailwind yang sudah ada, jadi tampilan kiosk ikut berubah tanpa mengubah semua komponen.
- Log hanya mencatat pembayaran (aktivitas lain, termasuk aksi admin, tidak dicatat lagi).
- Dashboard: pendapatan hari ini/total, per metode (QRIS/Voucher), grafik 7 hari, pembayaran terbaru.
- Log Pembayaran: filter (antrian, metode, tanggal), urut kolom, total ringkasan, ekspor CSV.
- Pengaturan: durasi sesi, PIN kasir; password SMTP tidak lagi tampil di HTML.
- Login dibatasi 6 percobaan/menit; pesan error bahasa Indonesia.
- Daftar frame: kolom harga dan urutan.

## Pembaruan tampilan & UX kiosk
- Layar awal dan tutorial dirapikan: info jumlah frame, harga mulai, estimasi durasi, serta langkah yang sesuai alur sebenarnya.
- Header + stepper dibuat lebih jelas dan responsif; sisa waktu berubah merah saat ≤ 1 menit.
- Keranjang frame: grid responsif (2 kolom di ponsel), badge jumlah, tombol kosongkan, dan validasi tombol lanjut.
- Sesi foto: progres foto, pesan error yang jelas, tombol coba lagi kamera, dan pengiriman semua slot foto sesuai urutan.
- Review: banner pembayaran/voucher, ringkasan pesanan (metode, kode voucher, total), serta Cetak/Unduh/Kirim email tetap di satu halaman. Email yang berhasil tidak lagi melempar pelanggan ke halaman lain.
- Selesai: tampilan konfirmasi baru dengan tombol unduh dan sesi baru.
- Sesi kedaluwarsa kini ditegakkan di server pada semua langkah; foto dibersihkan dan pelanggan diarahkan ke layar awal dengan pesan.
- Validasi unggahan foto/email diperketat (harus gambar valid dan ukurannya dibatasi).
- Timer habis: overlay + hitung mundur 10 detik sebelum otomatis kembali ke layar awal.

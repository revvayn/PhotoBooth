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
- Timer sesi tidak lagi ke-reset di tiap langkah (dihitung dari server). Durasi bisa diatur di Admin > Pengaturan > Sesi & Kasir (2–60 menit).
- Pembayaran QRIS dilakukan sebelum sesi foto, dan setelah foto selesai pelanggan tetap mendapat waktu untuk cetak/kirim.
- Tombol Kembali di tiap langkah (Ganti metode, Ganti Frame, Foto Ulang).
- Kamera hanya menyala di langkah Foto.
- Konfirmasi bayar tidak bisa dobel (klik ganda/refresh). PIN kasir opsional di Pengaturan.
- Review: tombol Cetak (sesuai jumlah lembar yang dibayar), Unduh, dan kirim email dengan foto terlampir.
- Email yang gagal kini menampilkan pesan error (dulu tetap dianggap berhasil).
- Foto pelanggan dihapus dari server saat sesi selesai / habis.
- QR invoice memakai nomor antrian yang benar.

## Admin
- Voucher: buat/edit/hapus kode dengan dua tipe:
  - **Cash**: satu kode voucher tunggal (perilaku lama).
  - **Event**: untuk acara seperti pernikahan. Atur nama event, tanggal, jumlah foto per voucher, dan jumlah orang. Sistem otomatis membuat kode voucher unik untuk setiap orang (mis. WEDDING-001, WEDDING-002, dst.).
- Halaman daftar voucher memiliki sub-menu **Cash** dan **Event** di sidebar tepat di bawah menu Voucher. Menu Voucher sendiri menampilkan semua voucher, sedangkan sub-menu memfilter per tipe.
- Cetak Invoice dan Cetak Voucher tersedia untuk setiap kode voucher dari halaman daftar voucher. Tampilan cetak dihapuskan sidebar admin agar bersih saat dicetak/di-export ke PDF.
- Tampilan: kini lebih sederhana, fokus pada identitas brand (nama, tagline, teks sambutan, footer, logo, gambar latar). Warna kini memakai palet default yang konsisten (rose/violet) agar tampilan tetap rapi.
  - Branding kini ikut diterapkan ke **sidebar & login admin, halaman cetak invoice/voucher, email hasil foto**, serta seluruh panel kiosk (termasuk badge status, tombol, ring, border, shadow, dan gradient).
  - Pemetaan warna diperluas: rose/pink → primary, emerald/green → success, violet/teal/blue/indigo/sky/cyan → secondary, fuchsia/purple → accent, amber/orange/yellow → warning, red → danger.
- Log hanya mencatat pembayaran (aktivitas lain, termasuk aksi admin, tidak dicatat lagi).
- Dashboard: pendapatan hari ini/total, per metode (QRIS/Voucher), grafik 7 hari, pembayaran terbaru.
- Log Pembayaran: filter (antrian, metode, tanggal), urut kolom, total ringkasan, ekspor CSV.
- Pengaturan waktu kini dibagi per tahap di Admin > Pengaturan > Sesi & Kasir:
  - **Durasi Pilih Metode & Frame** (default 10 menit): waktu untuk memilih metode pembayaran, frame, dan konfirmasi QRIS sebelum waktu habis.
  - **Durasi Sesi Foto** (default 5 menit): waktu pelanggan untuk mengambil semua foto setelah pembayaran dikonfirmasi.
  - **Durasi Review & Kirim** (default 5 menit): waktu pelanggan untuk review, cetak, unduh, dan kirim email setelah foto selesai.
- Pengaturan lama `session_seconds` tetap dipertahankan untuk kompatibilitas dan diisi otomatis berdasarkan durasi pilihan.
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

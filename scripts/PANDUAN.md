# Shortcut Photobooth (mode app, tanpa address bar)

## Sekali install
Jalankan dari folder proyek:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\create-shortcuts.ps1 -InstallAutostart
```

Ini membuat:
- `Photobooth (Lokal)` di Desktop & Start Menu -> server lokal + Edge mode app.
- `Photobooth (Testing Ngrok)` di Desktop & Start Menu -> memakai `APP_URL` dari `.env`.
- `Photobooth Server` di Startup -> MySQL + server PHP otomatis jalan saat Windows login (hidden, tanpa jendela terminal).

## Pemakaian
- Klik shortcut **Lokal** untuk pemakaian sehari-hari: `http://127.0.0.1:8000`.
- Klik shortcut **Testing Ngrok** hanya saat user perlu akses dari luar.
- Tidak perlu buka XAMPP Control Panel:
  - **Apache tidak dipakai** (Laravel memakai `php artisan serve`).
  - **MySQL tetap harus jalan** karena database aplikasi. Script otomatis menyalakan `mysqld` XAMPP bila port 3306 belum aktif.
- Kalau port sudah aktif, script tidak menyalakan proses ganda.

## Catatan ngrok
Ngrok sekarang **ikut aktif otomatis** setiap `Photobooth.exe` dibuka, memakai domain statis:
```
https://metalinguistic-unvenerably-fernande.ngrok-free.dev
```
Jadi device admin dan device photobooth cukup membuka URL itu, tidak perlu update link.

Kalau sudah tidak diperlukan:
1. Beri tanda `#` pada baris ngrok di `scripts\start-server.vbs`.
2. Ubah `.env`: `APP_URL=http://127.0.0.1:8000`.
3. Matikan proses ngrok.

Catatan: ngrok gratis tetap menampilkan halaman konfirmasi “Visit Site” sekali per browser. Itu normal — klik saja tombol tersebut.

## Kalau PHP / MySQL tidak ditemukan
Edit `scripts\start-server.vbs`:
```vbs
phpExe = "C:\xampp\php\php.exe"
xamppRoot = "C:\xampp"
```
Script mencari otomatis di `C:\xampp` dan `D:\xampp`. Kalau MySQL dipasang sebagai Windows service (bukan XAMPP portable), service tersebut tetap harus dijalankan manual/otomatis lewat Services.

## Ukuran jendela
Default `1366,768`. Ubah `--window-size` di `photobooth-local.bat` / `photobooth-ngrok.bat` bila perlu.
Untuk kiosk fullscreen tanpa title bar, ganti `--app=` menjadi `--kiosk` pada shortcut kiosk (jangan dipakai untuk admin).

## Versi Electron (.exe asli - dipakai sekarang)
Tidak butuh Rust / MSVC. Cukup npm.

```powershell
npm run electron:dev      # coba jalankan jendela app
npm run electron:build    # buat installer
```

Installer NSIS ada di:
```
release\Photobooth-Setup-1.0.0.exe
```

Shortcut **Photobooth** di Desktop & Start Menu mengarah ke aplikasi yang terpasang:
```
%LOCALAPPDATA%\Programs\Photobooth\Photobooth.exe
```
Hasil build mentah ada di `release\win-unpacked\Photobooth.exe` (cadangan). Setelah rebuild, jalankan installer `release\Photobooth-Setup-1.0.0.exe` lalu `scripts\create-electron-shortcut.ps1` untuk memastikan shortcut tetap benar.

Perilaku:
- Jendela `Photobooth` 1366x768, ikon sendiri, tanpa address bar/tab browser.
- Otomatis menjalankan **MySQL + PHP server + ngrok** lewat `scripts/start-server.vbs` sebelum jendela muncul.
- Ngrok memakai domain statis: `https://metalinguistic-unvenerably-fernande.ngrok-free.dev`. URL ini tidak berubah walaupun PC di-restart.
- Cek tunnel: buka `http://127.0.0.1:4040` (web interface ngrok). Kalau port 4040 aktif, tunnel siap dipakai device lain.
- Izin kamera diberikan otomatis untuk kebutuhan photobooth.
- Menu kecil (Edit / Tampilan / Jendela) agar tetap terasa seperti software, bukan browser.
- Klik “Buka Kiosk” membuka jendela localhost baru; link luar dibuka di browser default.

Catatan: karena belum di-sign, installer mungkin memicu peringatan SmartScreen saat pertama dijalankan. Pilih **More info -> Run anyway** hanya jika file berasal dari build lokal ini.

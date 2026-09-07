# ICHA - International Conference Academic Management System

Sistem Manajemen Konferensi Ilmiah Internasional berbasis web yang modern, aman, dan siap produksi. Dibangun menggunakan arsitektur **Laravel 11**, **Inertia.js**, **Vue 3**, dan **Tailwind CSS**, platform ini dirancang khusus untuk mengelola seluruh siklus konferensi ilmiah mulai dari pendaftaran peserta, pembayaran berjenjang, penelaahan naskah bertingkat (*Double-Blind Peer Review*), hingga penerbitan e-sertifikat resmi.

---

## 🚀 Fitur Utama

### 1. Portal Peserta (*Participant Portal*)
- **Pendaftaran Fleksibel**: Mendukung paket tiket *Presenter* dan *Non-Presenter* dengan tarif *Early Bird* maupun *Regular*.
- **Faktur Otomatis (*Invoice Engine*)**: Pembuatan nomor faktur berurutan (`INV-001`, `INV-002`, dst.) secara atomik disertai pengiriman email resmi.
- **Unggah Bukti Bayar**: Pengunggahan bukti transfer bank, pelacakan status verifikasi pembayaran, serta notifikasi email persetujuan/penolakan otomatis.
- **Pengajuan Abstrak (*Call for Abstracts*)**: Pengajuan naskah abstrak ilmiah (`ABS-001`, `ABS-002`, dst.) berdasarkan kategori/topik konferensi dengan pilihan presentasi (*Oral / Poster*).
- **Pengajuan Naskah Lengkap (*Full Paper*)**: Dibuka khusus bagi peserta berbayar (paket *Presenter*) yang naskah abstraknya telah dinyatakan **Accepted** (`FP-001`, `FP-002`, dst.).
- **Siklus Revisi Terkendali**: Mendukung alur *revision required*, pembuatan ronde telaah (*Review Round*) baru secara dinamis, dan penghapusan berkas lama secara otomatis saat revisi diunggah.
- **Unduh E-Sertifikat Resmi**: Penerbitan sertifikat digital yang dapat diverifikasi dan dialirkan langsung dalam format PDF resmi atau tampilan cetak.

### 2. Sistem Penelaahan Naskah (*Double-Blind Peer Review*)
- **Penilaian Anonim Ganda**: Informasi identitas penulis (*name*, *email*, *institution*, *phone*) disembunyikan sepenuhnya dari penelaah (*reviewer*).
- **Konsensus Mutlak 3 Reviewer**: Tepat 3 penelaah ditugaskan per naskah. Ronde telaah hanya dapat terkunci jika ketiga penelaah telah menuntaskan review secara penuh (3/3 completed).
- **Kalkulasi Rekomendasi Otoritatif di Server**: Skor kriteria ganda (1–5) dijumlahkan di backend; total skor $\ge 5 \rightarrow$ *Oral Presentation*, $< 5 \rightarrow$ *Poster Presentation* secara anti-manipulasi.
- **Wewenang Putusan Akhir Admin**: Rekomendasi reviewer bersifat saran ilmiah (*advisory*); Admin menetapkan keputusan final (*Accepted Oral/Poster*, *Revision Required*, *Rejected*) setelah ronde terkunci.
- **Anti-Conflict of Interest (COI)**: Sistem secara ketat melarang penelaah menilai naskah karya mereka sendiri.

### 3. Panel Kendali Panitia (*Admin Control Center*)
- **Manajemen Konferensi**: Pengaturan konferensi aktif, linimasa kegiatan, pembicara utama (*Keynote Speakers*), sponsor, serta template berkas Word/PDF.
- **Verifikasi Pembayaran & Immutability**: Pembayaran terverifikasi dibekukan secara permanen dari penghapusan, penolakan, atau modifikasi. Tombol verifikasi dilengkapi *idempotent guard* untuk mencegah duplikasi email.
- **Penugasan 3 Reviewer**: Penugasan tepat 3 penelaah per ronde telaah berdasarkan kesesuaian kategori topik.
- **Penerbitan Sertifikat Massal**: Unggah sertifikat resmi dan pelacakan status penerbitan peserta.
- **Manajemen Pengguna (Super Admin)**: Hak akses khusus untuk mengatur staf, admin, dan penelaah.

---

## 🛡️ Arsitektur Keamanan & Anti-Race Condition

Platform ini telah diaudit dan diperkuat dengan standar industri sebelum deploy:
1. **Atomic Cache Locking (`Cache::lock`)**: Mencegah serangan *race condition* dan *double-click submit* pada registrasi, unggah berkas, verifikasi pembayaran, dan submit review.
2. **Eliminasi Celah TOCTOU (*Time-of-Check to Time-of-Use*)**: Nomor urut kode (`ABS-xxx`, `FP-xxx`, `INV-xxx`) digenerate dan di-insert secara atomik di dalam lock database via `CodeGenerator::create`.
3. **Database-Level Integrity**:
   - `unique('review_assignment_id')` pada tabel `reviews`.
   - `unique('registration_id')` pada tabel `payments`.
   - `unique('abstract_id')` pada tabel `full_papers`.
   - `unique('abstract_code')` pada tabel `abstracts`.
   - `unique('paper_code')` pada tabel `full_papers`.
4. **Pembekuan Transaksi Pembayaran & State Machine Eksplisit**:
   - Pembayaran berstatus `verified` dibekukan secara permanen (tidak dapat diubah, ditolak, ditimpa, atau dihapus).
   - Validasi berkas bukti bayar dilakukan sebelum penyimpanan ke disk untuk menghemat *inodes* dan storage di shared hosting.
   - Status pendaftaran dikontrol secara ketat menggunakan state machine eksplisit `transitionTo()`.
5. **Validasi Anti-IDOR & Integritas Konferensi**:
   - Pengecekan ketat bahwa entitas (`registration`, `abstract`, `certificate`) benar-benar milik pengguna yang sedang terautentikasi.
   - Pengecekan silang (`cross-conference validation`) pada `registration_fee_id`, `category_id`, dan `abstract_id` untuk mencegah manipulasi harga tiket atau asosiasi abstrak dari konferensi yang tidak aktif.
6. **Anti-Conflict of Interest (Anti-COI) Reviewer**: Mencegah reviewer me-review naskah buatannya sendiri atau admin menugaskan author ke naskahnya sendiri.
7. **Pengiriman Email Instan (Synchronous Delivery)**: Email invoice dan konfirmasi verifikasi langsung dikirimkan seketika via SMTP/Resend tanpa ketergantungan pada background worker atau cron job, sangat andal untuk Shared Hosting.
8. **Rollback Berkas Aman**: Berkas baru otomatis dihapus dari disk penyimpanan jika transaksi database mengalami kegagalan (*anti-storage leak*).
9. **Alat Pemeliharaan Storage**: Dilengkapi perintah CLI untuk memangkas berkas *orphan* di storage:
   ```bash
   php artisan storage:prune-orphans --force
   ```
10. **Suite Pengujian Keamanan & IDOR**: Terintegrasi automated feature test (`tests/Feature/IdorAuthorizationTest.php`) untuk validasi IDOR, cross-conference spoofing, anti-COI, dan pengiriman email.

---

## 🛠️ Tumpukan Teknologi (*Tech Stack*)

- **Backend Framework**: Laravel 11 (PHP 8.2+)
- **Frontend Architecture**: Inertia.js (SPA Adapter) + Vue 3 (Composition API)
- **Styling**: Tailwind CSS, PostCSS, Lucide/Heroicons
- **Database**: MySQL / MariaDB (Mendukung SQLite & PostgreSQL)
- **Asset Bundler**: Vite
- **Email Service**: SMTP / Resend Custom Mail Transport

---

## 📦 Panduan Instalasi Lokal (*Local Setup*)

### Prasyarat
- PHP >= 8.2 (dengan ekstensi: `pdo_mysql`, `fileinfo`, `gd`, `zip`, `mbstring`, `curl`)
- Composer >= 2.0
- Node.js >= 18.x & NPM
- MySQL Server (misal Laragon, XAMPP, atau Docker)

### Langkah-langkah
1. **Clone repositori dan masuk ke direktori**:
   ```bash
   cd c:/laragon/www/Icha
   ```

2. **Install dependensi backend & frontend**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   Salin berkas `.env.example` ke `.env` jika belum ada:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Pastikan pengaturan database di `.env` sudah sesuai:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=icha
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate --seed
   ```

5. **Buat Symlink Storage Publik**:
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Server Lokal**:
   Terminal 1 (Laravel backend):
   ```bash
   php artisan serve
   ```
   Terminal 2 (Vite HMR):
   ```bash
   npm run dev
   ```

Akses aplikasi di browser pada: `http://127.0.0.1:8000`

---

## 🔑 Akun Bawaan (*Default Credentials*)

Setelah menjalankan `php artisan db:seed`, akun berikut siap digunakan:

| Peran (*Role*) | Alamat Email | Kata Sandi | Hak Akses Utama |
|---|---|---|---|
| **Super Admin** | `superadmin@icha.com` | `password` | Kelola seluruh konferensi, pengguna, dan sistem |
| **Admin** | `admin@icha.com` | `password` | Verifikasi bayar, penugasan reviewer, naskah, sertifikat |
| **Reviewer** | `reviewer@gmail.com` | `password` | Menelaah naskah naskah sesuai kategori keahlian |
| **Participant** | `participant@icha.com` | `password` | Pendaftaran, upload abstrak/paper, download sertifikat |

---

## ⚡ Perintah Artisan Berguna

```bash
# Bersihkan file sampah/orphan di storage yang tidak terhubung ke DB
php artisan storage:prune-orphans --force

# Jalankan pengujian unit dan race condition (dalam isolasi sqlite in-memory)
php artisan test --filter=RaceConditionSafetyTest

# Optimasi cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🌐 Checklist Siap Deploy (*Shared Hosting / VPS*)

Sebelum mengarahkan domain publik ke server produksi:

1. **Pengaturan `.env` Produksi**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-konferensi-anda.com
   ```
2. **Limit Ukuran Berkas di `php.ini`**:
   Karena berkas Full Paper (PDF/Word) berukuran cukup besar, pastikan batas unggah di server hosting dinaikkan:
   ```ini
   upload_max_filesize = 20M
   post_max_size = 25M
   max_execution_time = 120
   memory_limit = 256M
   ```
3. **Kompilasi Aset Frontend**:
   ```bash
   npm run build
   ```
4. **Hak Akses Folder (*Folder Permissions*)**:
   Pastikan folder `storage/` dan `bootstrap/cache/` dapat ditulisi (*writeable*):
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
5. **Symlink Storage**:
   Pastikan folder `public/storage` mengarah ke `storage/app/public` via:
   ```bash
   php artisan storage:link
   ```
6. **Pengiriman Email di Shared Hosting**:
   Aplikasi dikonfigurasikan dengan `QUEUE_CONNECTION=sync` sehingga email langsung dikirimkan seketika (instant) tanpa membutuhkan Cron Job atau daemon latar belakang.
   Pastikan kredensial SMTP atau API Resend di `.env` sudah terisi dengan benar.

---

## 📄 Lisensi

Sistem ini dikembangkan secara eksklusif untuk penyelenggaraan konferensi ilmiah internasional ICHA (*International Conference on Health and Applied Sciences*). Hak Cipta dilindungi undang-undang.

# Panduan Data Factory & Seeding — ICHA Conference System

Dokumen ini menjelaskan strategi pengelolaan data awal (**Seeder**) dan data simulasi uji coba (**Model Factory**) untuk aplikasi ICHA (International Conference on Healthcare Administration).

---

## 1. Prinsip & Strategi Pengelolaan Data

Sistem membedakan data menjadi 2 kategori utama:

1. **Master Data & Konfigurasi Acara (Deterministic / Fixed Data)**:
   - Data resmi yang menjadi pondasi berjalannya konferensi (Edisi Konferensi, Akun Panitia Inti, Akun Reviewer Terverifikasi, Kategori/Track, Jadwal Timeline, Daftar Pembicara, Sponsor, dan Tarif Registrasi).
   - **Dikelola Melalui**: `DatabaseSeeder.php` dan `ConferenceSeeder.php`.
   - **Tidak Memerlukan Factory acak** agar konten acara di landing page tetap faktual dan kredibel.

2. **Data Transaksional & Pengujian Beban (High Volume / Dynamic Data)**:
   - Data aktivitas pengguna yang bertambah seiring berjalannya acara (Pendaftaran Peserta, Invois, Bukti Pembayaran, Naskah Abstrak, Penilaian Reviewer, dan Naskah Lengkap).
   - **Dikelola Melalui**: **Laravel Model Factories** & `DummyDataSeeder.php` (Opsional untuk Testing/Demo).

---

## 2. Matriks Kebutuhan Factory per Modul

| Modul / Model | Status Kebutuhan | Alasan & Manfaat Penggunaan |
| :--- | :---: | :--- |
| **`User` + `Profile`** | ✅ **Perlu Factory** | Menguji **Pagination 20 data per halaman** di menu User Management, filter peran (*Super Admin, Admin, Reviewer, Participant*), dan pencarian instan. |
| **`Registration`** | ✅ **Perlu Factory** | Menghasilkan riwayat pendaftaran dengan nomor invois berurutan (`INV-YYYY-MM-XXXX`) dan tarif presenter/non-presenter. |
| **`Payment`** | ✅ **Perlu Factory** | Menguji filter status verifikasi (*Pending, Verified, Rejected*) dan kalkulasi total uang masuk di grafik Admin Dashboard. |
| **`AbstractSubmission`** | ✅ **Perlu Factory** | Menguji manajemen daftar naskah abstrak (`ABS-XXX`), penugasan otomatis ke 3 reviewer berdasarkan kategori, dan pembatasan duplikasi naskah. |
| **`ReviewRound` & `Review`** | ✅ **Perlu Factory** | Simulasi penelaahan oleh 3 reviewer independen, kalkulasi skor kriteria 1–5, dan penguncian ronde (*Locked at 3/3 completed*). |
| **`Conference`** | ❌ **Tidak Perlu** | Edisi tahunan konferensi diatur manual oleh Super Admin (misal: ICHA 2026, ICHA 2027). |
| **`Category`, `Speaker`, `Timeline`, `Sponsor`** | ❌ **Tidak Perlu** | Master data konten kurasi panitia. Lebih tepat dikelola via Seeder resmi atau CMS Admin. |
| **`Certificate`** | ❌ **Tidak Perlu** | Terbit otomatis secara internal oleh sistem (*on-demand*) saat pembayaran diverifikasi. |

---

## 3. Desain & Struktur Model Factories

### A. `UserFactory`
Digunakan untuk menghasilkan user dengan role tertentu:
```php
// Contoh State di UserFactory:
User::factory()->participant()->create(); // Role: participant
User::factory()->reviewer()->create();    // Role: reviewer
User::factory()->admin()->create();       // Role: admin
```

### B. `RegistrationFactory`
Menghasilkan pendaftaran yang terhubung dengan `RegistrationFee` dan `Conference`:
```php
Registration::factory()->presenter()->create();
Registration::factory()->nonPresenter()->create();
```

### C. `PaymentFactory`
Menghasilkan transaksi pembayaran dengan berbagai status:
```php
Payment::factory()->pending()->create();
Payment::factory()->verified()->create();
Payment::factory()->rejected()->create();
```

### D. `AbstractSubmissionFactory`
Menghasilkan naskah dengan format kode `ABS-XXX` dan status workflow yang valid:
```php
AbstractSubmission::factory()->underReview()->create();
AbstractSubmission::factory()->accepted('oral')->create();
```

---

## 4. Pemisahan Lingkungan (Environment Segregation)

Untuk menjaga integritas database antara pengujian lokal dan server produksi:

### 1. Lingkungan Produksi / Live Hosting (`Production`)
Hanya menjalankan seeder resmi untuk mempersiapkan sistem:
```bash
# Menyiapkan akun Super Admin, Reviewer resmi, dan Master Data Konferensi
php artisan migrate --force
php artisan db:seed --force
```

### 2. Lingkungan Pengembangan & Pengujian UI (`Local / Staging`)
Jika panitia atau developer ingin mengisi database dengan 50 peserta dummy untuk menguji pagination & dashboard:
```bash
# Menjalankan seeder simulasi khusus
php artisan db:seed --class=DummyDataSeeder
```

---

## 5. Manfaat Implementasi bagi Tim
1. **Memastikan Pagination Berjalan Mulus**: Memvalidasi batas 20 data per halaman di menu admin tanpa input data manual berulang kali.
2. **Stress Testing**: Memastikan performa query Eloquent tetap cepat saat jumlah peserta mencapai ratusan.
3. **Demo Visual Menarik**: Dashboard langsung terisi statistik grafik uang masuk, perbandingan naskah Oral vs Poster, dan progress review 3/3 saat dipresentasikan.

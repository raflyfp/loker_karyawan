# DOKUMENTASI SISTEM LOKER OTOMATIS — SNA MEDIKA
Panduan Teknis Arsitektur, Alur Kerja, dan Pemeliharaan untuk Pengembang (Junior Developer).

---

## 1. Arsitektur MVC & Pemisahan Tanggung Jawab

Aplikasi dibangun menggunakan **Laravel 12 (PHP 8.2+)**, **MySQL (InnoDB)**, serta **Tailwind CSS v4 & Vite**.

```
app/
├── Console/Commands/
│   └── SyncKaryawanCommand.php      # Perintah Artisan 'loker:sync-karyawan'
├── Http/
│   ├── Controllers/
│   │   ├── LokerController.php      # Controller view antarmuka terminal loker
│   │   └── LokerScanController.php  # Controller API scan, status map, & toggle loker
│   └── Requests/
│       └── ScanLokerRequest.php     # Form request validasi input barcode & gender
├── Models/
│   ├── Karyawan.php                 # Master data karyawan (relasi alokasi & status)
│   ├── Loker.php                    # Master loker (1..100 Laki-laki & Perempuan)
│   └── AlokasiLoker.php             # Transaksi alokasi aktif & riwayat pengembalian
└── Services/
    ├── KaryawanSyncService.php      # Service integrasi HTTP Client API SNA Medika
    └── LokerAllocationService.php   # Service logika transaksi, locking, TTS, alokasi/lepas
```

### Prinsip Utama:
- **Controller Ramping**: Controller hanya menerima input HTTP, memanggil Form Request, mendelegasikan proses bisnis ke Service, dan mengembalikan JSON/View.
- **Service Khusus**: Seluruh logika alokasi loker, pelepasan, row-level locking (`lockForUpdate`), dan deduplikasi berada di `LokerAllocationService`.
- **Database Transaction**: Proses alokasi dan pelepasan loker dibungkus dalam `DB::transaction()` dengan isolasi ketat.

---

## 2. Struktur Tabel & Relasi Database

### A. Tabel `karyawan`
Menyimpan master data karyawan yang disinkronkan dari API Absen SNA Medika.
- `id` (PK)
- `nik` (VARCHAR 50, UNIQUE) — Menyimpan string NKK/NIK ID Card (tidak boleh numeric agar angka 0 di depan tidak terpotong).
- `nama` (VARCHAR 150)
- `divisi` (VARCHAR 100, NULL)
- `gender` (ENUM: `'L'`, `'P'`, NULL)
- `is_active` (BOOLEAN, DEFAULT 1)
- `synced_at` (TIMESTAMP)

### B. Tabel `lokers`
Menyimpan 100 loker Laki-laki dan 100 loker Perempuan.
- `id` (PK)
- `kode_loker` (VARCHAR 20, UNIQUE) — Contoh: `L-1`, `P-1`
- `nomor_urut` (INT UNSIGNED) — 1 s/d 100 (sorting numerik akurat)
- `gender_group` (ENUM: `'L'`, `'P'`)
- `is_active` (BOOLEAN, DEFAULT 1) — Dapat diubah menjadi 0 jika loker rusak/dikunci dari master.

### C. Tabel `alokasi_lokers`
Mencatat riwayat transaksi dan alokasi aktif.
- `id` (PK)
- `karyawan_id` (FK -> `karyawan.id`)
- `loker_id` (FK -> `lokers.id`)
- `waktu_masuk` (TIMESTAMP)
- `waktu_keluar` (TIMESTAMP, NULL saat masih aktif)
- `status` (ENUM: `'active'`, `'completed'`, `'cancelled'`)
- **Generated Columns & Unique Constraints**:
  - `active_karyawan_id` = `CASE WHEN status = 'active' THEN karyawan_id ELSE NULL END`
  - `active_loker_id` = `CASE WHEN status = 'active' THEN loker_id ELSE NULL END`
  - `UNIQUE (active_karyawan_id)`: **Menjamin 1 karyawan hanya memiliki 1 loker aktif**.
  - `UNIQUE (active_loker_id)`: **Menjamin 1 loker tidak dialokasikan ke 2 orang sekaligus**.

---

## 3. Konfigurasi API SNA Medika

Pengaturan API disimpan di `config/services.php` dan dapat disesuaikan melalui file `.env`:

```env
SNAM_KARYAWAN_API_URL=https://www.snam110.dpdns.org/absen/api/karyawan
SNAM_API_TIMEOUT=30
```

---

## 4. Cara Menjalankan Sinkronisasi Master Karyawan

Gunakan perintah Artisan di terminal:
```bash
php artisan loker:sync-karyawan
```

Perintah ini akan:
1. Mengambil data seluruh karyawan dari endpoint API SNA Medika.
2. Melakukan upsert ke tabel lokal `karyawan` berdasarkan `nik`.
3. Mempertahankan pemetaan gender lokal yang sudah ditentukan sebelumnya.
4. Mencatat statistik sinkronisasi secara aman tanpa mencetak data pribadi ke log.

---

## 5. Cara Menambah atau Menonaktifkan Loker

### Mengisi Loker Awal:
Loker 1 s/d 100 Laki-laki dan 1 s/d 100 Perempuan digenerate otomatis melalui Seeder:
```bash
php artisan db:seed --class=LokerSeeder
```

### Mengunci / Menonaktifkan Loker Tertentu (Maintenance/Rusak):
- **Melalui Antarmuka Kiosk**: Klik/sentuh kotak loker yang ingin dikunci, lalu tekan tombol **"Kunci / Nonaktifkan Loker"**.
- **Melalui API**:
  ```bash
  POST /api/loker/{id}/toggle-active
  ```
  *(Loker yang sedang aktif digunakan tidak dapat dinonaktifkan sampai dikembalikan).*

---

## 6. Cara Menjalankan Aplikasi di Lingkungan Lokal

1. **Pastikan Laragon berjalan** (MySQL & Apache).
2. **Jalankan Migrasi & Seeder**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=LokerSeeder
   php artisan loker:sync-karyawan
   ```
3. **Build Frontend**:
   ```bash
   cmd /c npm run build
   ```
4. **Buka di Browser**:
   Kunjungi URL lokal:
   `http://localhost/loker-karyawan/public` atau `http://loker-karyawan.test`

---

## 7. Cara Menguji Scanner Fisik dan Kamera

### A. Scanner Barcode Fisik (USB / Handheld):
- Sambungkan scanner ke port USB komputer/kiosk monitor.
- Buka halaman kiosk `http://localhost/loker-karyawan/public`.
- Halaman memiliki fitur **Autofocus Cerdas**: kursor otomatis fokus ke kolom scan tanpa perlu diklik.
- Pindai barcode pada ID Card. Scanner akan mengirim string NKK dan karakter `Enter`.
- Sistem langsung memproses transaksi, menampilkan nomor loker, memutar suara notifikasi, dan mengembalikan fokus input.

### B. Kamera Browser:
- Klik tombol **"Kamera"** berwarna biru di kanan atas.
- Izinkan akses kamera pada browser (*Grant Camera Permission*).
- Arahkan barcode ke kotak pemindai kamera.
- Library `html5-qrcode` akan mendeteksi barcode, menutup kamera secara otomatis, dan memproses transaksi.

---

## 8. Logika Transaksi: Scan Masuk & Pulang

1. **Scan Pertama (Masuk / Alokasi)**:
   - Server memeriksa apakah karyawan memiliki alokasi aktif.
   - Jika belum ada, server memilih loker kosong bernomor urut terkecil pada kelompok gender yang sesuai (`L` atau `P`).
   - Loker dikunci di database dan dicatat waktu masuk.
   - Tampilan menampilkan nomor loker besar (misal: **12**) dan audio browser membacakan:  
     *"Selamat datang, [Nama]. Silakan menggunakan loker nomor dua belas."*

2. **Scan Kedua (Pulang / Pelepasan)**:
   - Server mendeteksi karyawan sudah memiliki loker aktif.
   - Status alokasi diubah menjadi `completed` dan dicatat waktu keluar.
   - Loker seketika tersedia kembali di peta untuk digunakan orang lain.
   - Audio membacakan konfirmasi:  
     *"Terima kasih, [Nama]. Loker nomor dua belas telah dikembalikan."*

3. **Karyawan dengan Gender Belum Dipetakan**:
   - Jika data karyawan memiliki `gender = NULL`, sistem menampilkan modal cepat:  
     *"Silakan pilih kelompok loker: [Laki-laki] atau [Perempuan]"*.
   - Saat karyawan memilih, pilihan tersebut langsung disimpan permanen di database dan loker dialokasikan seketika.

---

## 9. Cara Menjalankan Pengujian Otomatis (Automated Tests)

Jalankan test suite menggunakan PHPUnit / Pest bawaan Laravel:
```bash
php artisan test
```

Pengujian mencakup:
- Validasi konektivitas & penanganan error API SNA Medika.
- Alokasi loker nomor urut terkecil.
- Pelompatan loker yang sedang terisi atau nonaktif.
- Pelepasan loker saat scan kedua.
- Pembuatan alokasi baru pada scan ketiga.
- Penolakan transaksi saat kapasitas loker penuh.
- Integritas data dan pencegahan alokasi ganda.

---

## 10. Penanganan Masalah Umum (Troubleshooting)

| Masalah | Penyebab | Solusi |
| :--- | :--- | :--- |
| **Kamera tidak mau terbuka** | Izin kamera diblokir browser atau belum HTTPS/localhost. | Pastikan mengakses via `http://localhost/...` atau gunakan protokol HTTPS. Periksa *site settings* kamera di browser. |
| **Suara tidak berbunyi** | Kebijakan *Autoplay* browser membutuhkan interaksi pertama. | Klik sembarang area layar sekali saat kiosk dinyalakan agar Web Speech API dan AudioContext diizinkan browser. |
| **Error 422: Loker Penuh** | Semua 100 loker pada kelompok gender tersebut sedang terisi. | Periksa peta loker di layar. Lepaskan loker yang sudah tidak digunakan atau aktifkan loker yang terkunci. |
| **Barcode tidak terbaca scanner USB** | Input barcode kehilangan fokus keyboard. | Sistem memiliki timer auto-refocus setiap 1,5 detik. Pastikan tidak ada dialog sistem operasi lain yang aktif. |

---

## 11. Prosedur Deployment Produksi yang Aman

1. **Pastikan dependency terinstal**:
   ```bash
   composer install --no-dev --optimize-autoloader
   cmd /c npm ci
   cmd /c npm run build
   ```
2. **Jalankan migrasi database**:
   ```bash
   php artisan migrate --force
   ```
3. **Optimasi Cache Laravel**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. **Jalankan Sinkronisasi Awal Karyawan**:
   ```bash
   php artisan loker:sync-karyawan
   ```

# SIMOSIS

**SIMOSIS (Sistem Informasi Manajemen OSIS)** adalah aplikasi web untuk membantu sekolah mengelola data siswa, organisasi OSIS, kegiatan, tahun ajaran, serta administrasi kas OSIS dan kas kelas secara terpusat.

Aplikasi ini menggunakan PHP native dengan PDO dan MySQL/MariaDB. Antarmuka dibangun menggunakan Bootstrap 5, Bootstrap Icons, dan stylesheet khusus pada folder `assets/`.

## Fitur Utama

- **Autentikasi pengguna**
  - Login, registrasi, logout, dan pembatasan akses berdasarkan peran.
  - Pengalihan dashboard otomatis sesuai role dan jabatan pengguna.
- **Multi-role dashboard**
  - Administrator sekolah.
  - Pengurus OSIS, termasuk bendahara.
  - Pengurus kelas atau bendahara kelas.
  - Siswa.
- **Manajemen data sekolah**
  - Data siswa.
  - Data jurusan dan kelas.
  - Tahun ajaran aktif.
  - Riwayat kelas siswa.
- **Manajemen organisasi OSIS**
  - Data anggota OSIS.
  - Jabatan dan status keanggotaan.
  - Kegiatan, agenda, pengumuman, dan kepanitiaan.
  - Riwayat anggota serta penerbitan sertifikat.
- **Manajemen keuangan**
  - Kas OSIS.
  - Kas kelas berdasarkan kelas atau jurusan.
  - Pengaturan nominal dan frekuensi pembayaran.
  - Tagihan siswa, pembayaran, setoran, konfirmasi, dan transaksi.
- **Kenaikan kelas**
  - Pengelolaan proses kenaikan kelas.
  - Riwayat kenaikan kelas.
  - Dukungan status lulus dan nonaktif.
- **Dashboard dan visualisasi**
  - Ringkasan statistik data sekolah dan keuangan.
  - Grafik menggunakan Chart.js.
  - Tampilan responsif berbasis Bootstrap.

## Teknologi

- PHP 7.4+ atau PHP 8.x
- MySQL 8+ atau MariaDB
- PDO untuk koneksi database
- Bootstrap 5
- Bootstrap Icons
- Chart.js
- HTML, CSS, dan JavaScript

## Struktur Direktori

```text
.
├── admin/                 # Modul administrator
│   ├── jabatan_osis/      # Pengelolaan jabatan OSIS
│   ├── jurusan/           # Pengelolaan jurusan
│   ├── kas_siswa/         # Data kas siswa
│   ├── kenaikan_kelas/    # Proses dan riwayat kenaikan kelas
│   ├── keuangan/          # Ringkasan keuangan
│   ├── osis/              # Pengelolaan anggota OSIS dan sertifikat
│   ├── pengurus_kelas/    # Pengelolaan pengurus kelas
│   ├── siswa/             # Pengelolaan data siswa
│   └── tahun_ajaran/      # Pengelolaan tahun ajaran
├── assets/                # CSS, JavaScript, dan vendor frontend
├── auth/                  # Login, registrasi, dan logout
├── config/                # Konfigurasi database dan autentikasi
├── includes/              # Komponen layout bersama
├── osis/                  # Dashboard dan modul pengurus OSIS
├── pengurus_kelas/        # Dashboard dan modul pengurus kelas
├── siswa/                 # Dashboard siswa
├── img/                   # Logo dan aset gambar
├── index.php              # Router dashboard berdasarkan role pengguna
└── simosis (2).sql        # Dump struktur dan data database
```

## Instalasi Lokal

### 1. Siapkan web server

Pasang salah satu environment berikut:

- [XAMPP](https://www.apachefriends.org/)
- [Laragon](https://laragon.org/)
- Web server Apache/Nginx dengan PHP dan MySQL/MariaDB

Pastikan ekstensi PHP berikut aktif:

- `pdo`
- `pdo_mysql`
- `mbstring`
- `fileinfo` jika diperlukan oleh fitur unggah berkas

### 2. Clone repository

Letakkan project di web root, lalu jalankan:

```bash
git clone https://github.com/albukhorifirdaus09-ui/uangkass-simosis.git
cd uangkass-simosis
```

Contoh lokasi web root:

- XAMPP: `C:\xampp\htdocs\uangkass-simosis`
- Laragon: `C:\laragon\www\uangkass-simosis`

### 3. Buat database

Buat database MySQL/MariaDB dengan nama `simosis`, kemudian import file dump:

```bash
mysql -u root -p simosis < "simosis (2).sql"
```

Alternatifnya, import file `simosis (2).sql` melalui phpMyAdmin.

> File SQL merupakan dump database pengembangan. Tinjau dan sesuaikan data awal sebelum digunakan pada lingkungan produksi.

### 4. Konfigurasi koneksi database

Edit file `config/database.php` dan sesuaikan nilai berikut dengan konfigurasi lokal Anda:

```php
$host = "127.0.0.1";
$dbname = "simosis";
$username = "root";
$password = "";
```

Jangan menyimpan password database produksi di repository publik. Gunakan environment variable atau konfigurasi lokal yang tidak di-commit.

### 5. Jalankan aplikasi

Dengan Apache/XAMPP atau Laragon aktif, buka:

```text
http://localhost/uangkass-simosis/
```

Jika menggunakan PHP built-in server, jalankan dari direktori project:

```bash
php -S localhost:8000
```

Lalu buka `http://localhost:8000`.

## Alur Akses Pengguna

1. Pengguna membuka halaman aplikasi dan diarahkan ke `auth/login.php` apabila belum login.
2. Setelah login, `index.php` menentukan tujuan berdasarkan role:
   - `admin` → dashboard administrator.
   - email : admin@simosis.test pass: Admin123!
   - `siswa` → dashboard siswa atau dashboard pengurus kelas.
   - `osis` → dashboard anggota OSIS atau dashboard bendahara OSIS.
3. Setiap modul melakukan pemeriksaan autentikasi dan otorisasi sebelum menampilkan data.

## Catatan Keamanan

- Segera ganti kredensial akun awal setelah instalasi.
- Gunakan password yang kuat dan hash password menggunakan mekanisme PHP yang aman.
- Jangan mengunggah kredensial database, file konfigurasi produksi, atau data siswa sebenarnya ke repository publik.
- Gunakan HTTPS pada deployment produksi.
- Validasi dan batasi tipe serta ukuran file yang diunggah.
- Buat backup database secara berkala.

## Pengembangan

Project ini menggunakan struktur PHP sederhana tanpa dependency manager. Perubahan frontend dapat dilakukan pada:

- `assets/css/style.css`
- `assets/js/app.js`
- Komponen layout pada `includes/`

Perubahan koneksi database dilakukan pada `config/database.php`, sedangkan pemeriksaan role dan session berada pada `config/auth.php`.

## Status Project

SIMOSIS masih dikembangkan dan dapat disesuaikan dengan alur administrasi masing-masing sekolah. Sebelum digunakan secara resmi, lakukan pengujian menyeluruh terhadap autentikasi, hak akses, transaksi keuangan, unggah dokumen, dan proses kenaikan kelas.

## Lisensi

Lisensi belum ditentukan. Hubungi pemilik repository sebelum menggunakan, mendistribusikan, atau memodifikasi project ini untuk kebutuhan komersial.

---

Dikembangkan untuk mendukung digitalisasi administrasi OSIS dan pengelolaan kas sekolah.

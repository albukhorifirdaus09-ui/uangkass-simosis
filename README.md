# SIMOSIS - Sistem Informasi Manajemen OSIS & Administrasi Sekolah

**SIMOSIS** adalah aplikasi berbasis web yang dirancang khusus untuk mempermudah manajemen administrasi sekolah, mulai dari pengelolaan data siswa, organisasi OSIS, pengelolaan struktur pengurus kelas, hingga pencatatan kas/keuangan secara digital dan terstruktur.

Aplikasi ini hadir dengan **Antarmuka (UI) Premium**, mengusung desain modern (*glassmorphism*, *clean table*, & *premium typography*) yang sangat nyaman digunakan baik oleh admin, pengurus OSIS, maupun siswa biasa.

---

## ✨ Fitur Utama

### 1. 👥 Multi-Role Akses (Hak Akses Berbeda)
Aplikasi mendukung beberapa tipe akun (Role) yang masuk melalui satu portal login terpusat:
- **Admin/Pembina:** Memiliki kontrol penuh terhadap seluruh data master (Siswa, Tahun Ajaran, Jurusan, Keuangan).
- **Pengurus OSIS (Ketua, Wakil, Sekretaris, Bendahara):** Dapat mengelola kegiatan OSIS dan manajemen internal (Dashboard dibedakan sesuai jabatannya).
- **Bendahara Kelas:** Diberikan dashboard khusus untuk mengurus kas per rombel/kelas secara mandiri.
- **Siswa Biasa:** Dashboard personal untuk memantau status kas/keanggotaan mereka sendiri.

### 2. 📚 Otomatisasi Kenaikan Kelas & Manajemen Alumni
Sistem menangani tahun ajaran baru secara otomatis (Tanpa input manual berulang):
- Siswa kelas X otomatis naik ke kelas XI.
- Siswa kelas XI otomatis naik ke kelas XII.
- Siswa kelas XII secara otomatis dinyatakan **Lulus**, dan akun portal mereka akan dinonaktifkan.
- Posisi penting seperti *Bendahara Kelas* yang menjabat di tahun sebelumnya akan otomatis dipertahankan dan ikut naik ke kelas barunya.

### 3. 💳 Pengelolaan Keuangan & Kas (Dua Tingkat)
- **Kas OSIS Terpusat:** Manajemen arus kas masuk/keluar oleh bendahara OSIS.
- **Kas Kelas/Jurusan:** Manajemen uang kas mingguan/bulanan per siswa yang dikelola langsung oleh Bendahara Kelas masing-masing.

### 4. 📜 Sistem E-Sertifikat Cerdas (Otomatis & PDF)
- Admin dapat menerbitkan dan mencetak sertifikat penghargaan untuk anggota OSIS aktif atau purnabakti.
- Dilengkapi dengan *template* desain sertifikat yang sangat elegan (Bingkai Emas/Biru) bergaya klasik.
- Mendukung fitur langsung **Download sebagai File PDF** (Menggunakan `html2pdf.js`) tanpa perlu install software tambahan.

### 5. 🎨 UI/UX Premium (Desain Terkini)
Antarmuka pengguna didesain menggunakan framework CSS modern dengan sentuhan khusus:
- *Glassmorphism* (Efek blur transparan) pada navbar dan komponen melayang.
- Typografi modern menggunakan Google Fonts (**Plus Jakarta Sans**).
- *Hover Animations* & *Drop-shadows* yang sangat mulus layaknya aplikasi startup.

---

## 🛠️ Stack Teknologi

- **Backend:** PHP (Native/PDO)
- **Database:** MySQL / MariaDB
- **Frontend Framework:** Bootstrap 5
- **Icons:** Bootstrap Icons (`bootstrap-icons`)
- **Typography:** Google Fonts (Plus Jakarta Sans)
- **Library Tambahan:** `html2pdf.bundle.min.js` (Untuk ekspor sertifikat)

---

## 🚀 Cara Instalasi (Local Development)

1. **Persiapan Lingkungan (Environment):**
   Pastikan Anda sudah menginstal aplikasi web server seperti **XAMPP** atau **Laragon**.
   Disarankan menggunakan versi PHP 7.4 atau PHP 8.x.

2. **Kloning Proyek:**
   Download atau *clone* repository ini, lalu letakkan di folder web root Anda (misal: `C:\laragon\www\simosis` atau `C:\xampp\htdocs\simosis`).

3. **Pengaturan Database:**
   - Buat database baru di MySQL/phpMyAdmin (misalnya dengan nama `simosis`).
   - Import file `.sql` (jika tersedia) ke dalam database tersebut.
   - Buka folder `config/`, lalu cari file koneksi database (misal: `database.php` atau `koneksi.php`).
   - Sesuaikan *username*, *password*, dan *nama database* Anda.

4. **Jalankan Aplikasi:**
   Buka browser Anda dan akses `http://localhost/simosis`.

---

## 🔒 Default Akun Login

Secara bawaan, Anda dapat masuk menggunakan akun admin berikut untuk menguji sistem (Harap sesuaikan dengan data riil di tabel `users` Anda).

| Role / Jabatan | Username | Email | Password |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin@simosis.test` | `admin` |

---

## 📝 Catatan Rilis (Changelog Terbaru)

- **[UI Redesign]** Rombak total Sidebar, Navbar, dan Dashboard Cards menggunakan tema Slate-Dark, layout modern, dan integrasi font Plus Jakarta Sans.
- **[Fitur Kenaikan Kelas]** Implementasi UI premium untuk halaman data kenaikan kelas. Otomatisasi bawaan (`aktifkan.php`) untuk mutasi bendahara kelas dan penonaktifan akun kelas XII (Lulus).
- **[Sertifikat OSIS]** Redesain template sertifikat premium menggunakan *vintage borders* dan penambahan tombol *Download to PDF*.

---

*Dikembangkan dengan dedikasi untuk kemajuan administrasi digital sekolah.*
